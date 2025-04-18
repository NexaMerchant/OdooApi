<?php
namespace NexaMerchant\OdooApi\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Webkul\Product\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Webkul\Core\Rules\Slug;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NexaMerchant\OdooApi\Http\Controllers\Api\Controller;
use Symfony\Component\HttpKernel\HttpCache\Store;

class ProductsController extends Controller
{

    public function __construct(
        protected ProductRepository $productRepository,
    )
    {
        
    }

    public function index(Request $request)
    {
        return response()->json();
    }

    public function show(Request $request, $id)
    {
        return response()->json();
    }


    public function syncProductToLocal($shopify_pro_id) {

        $option1 = "color";
        $option2 = "size";

        $items = \Nicelizhi\Shopify\Models\ShopifyProduct::where("shopify_store_id", $this->shopify_store_id)->where("product_id", $shopify_pro_id)->get();
        foreach($items as $key=>$item) {
            $this->info($item['product_id']);

            $redis = Redis::connection('default');

            $images_map = [];

            $options = $item->options;
            $shopifyVariants = $item->variants;
            $shopifyImages = $item->images;
            
            foreach($shopifyImages as $key=>$shopifyImage) {
                //var_dump($shopifyImage);
                
                $images_map[$shopifyImage['id']] = $shopifyImage['src'];
                foreach($shopifyImage['variant_ids'] as $kk=>$variant_ids) {
                    //var_dump($variant_ids);
                    $images_map[$variant_ids] = $shopifyImage['src'];
                }
            }

            $color = [];
            $size = [];
            $error = 0;
            $LocalOptions = [];
            $LocalOptions = \Nicelizhi\Shopify\Helpers\Utils::createOptions($options);

           //var_dump($LocalOptions, $options);exit;

            $color = $LocalOptions['color'];
            $size = $LocalOptions['size'];
            //var_dump($LocalOptions);
            
            // add product

            $data = [];
            $data['attribute_family_id'] = 1;
            $data['sku'] = $item['product_id'];
            $data['type'] = "configurable";
            $super_attributes['color'] = $color;
            $super_attributes['size'] = $size;
            $data['super_attributes'] = $super_attributes;

            $method = "create";

            // check product info
            $product = $this->productRepository->where("sku", $item['product_id'])->first();
            if(is_null($product)) {
                Event::dispatch('catalog.product.create.before');
                $product = $this->productRepository->create($data);
                $id = $product->id;
                Event::dispatch('catalog.product.create.after', $product);
            }else{

                $method = "update";
                $id = $product->id;
   
            }

       
            \Nicelizhi\Shopify\Helpers\Utils::clearCache($id, $item['product_id']); // clear cache

            // update the sku sort
            foreach($LocalOptions['LocalOptions'] as $key=>$LocalOption) {
                $cache_key = "product_attr_sort_".$key."_".$id;
                echo $cache_key."\r\n";
                foreach($LocalOption as $k => $localOpt) {
                    $redis->hSet($cache_key, $localOpt,  $k);
                }
                //$redis->hSet($this->cache_key.$this->prod_id, $key, json_encode($value));
            }

            $variants = $variantCollection = $product->variants()->get()->toArray();

            //exit;
            $updateData = [];
            $updateData['product_number'] = "";
            $updateData['name'] = $item['title'];
            $updateData['url_key'] = $item['product_id'];
            $updateData['short_description'] = $item['title'];
            $updateData['description'] = $item['title'];
            $updateData['new'] = 1;
            $updateData['featured'] = 1;
            $updateData['visible_individually'] = 1;
            $updateData['status'] = 1;
            $updateData['guest_checkout'] = 1;
            $updateData['channel'] = $this->channel;
            $updateData['locale'] = $this->lang;
            $categories = [];
            $categories[] = $this->category_id;
            $updateData['categories'] = $categories;

            $updateData['description'] = $item['body_html'];

           // $updateData['compare_at_price'] = $item['compare_at_price'];
            $updateData['compare_at_price'] = $shopifyVariants[0]['compare_at_price'];
            $updateData['price'] = $shopifyVariants[0]['price'];

            $variants = $variantCollection = $product->variants()->get()->toArray();

            $newShopifyVarants = [];
            $compare_at_price = '0.00';
            foreach($shopifyVariants as $sv => $shopifyVariant) {
                //var_dump($shopifyVariant);
                $newkey = $shopifyVariant['product_id'];
                $color = AttributeOption::where("attribute_id", 23)->where("admin_name", $shopifyVariant['option1'])->first();
                if(is_null($color)) {
                    $option1 = "size";
                    $color = AttributeOption::where("attribute_id", 23)->where("admin_name", $shopifyVariant['option2'])->first();
                }
                $size = AttributeOption::where("attribute_id", 24)->where("admin_name", $shopifyVariant['option2'])->first();
                if(is_null($size)) {
                    $option1 = "color";
                    $size = AttributeOption::where("attribute_id", 24)->where("admin_name", $shopifyVariant['option1'])->first();
                }

                if(is_null($color) || is_null($size)) {
                    $this->info("error");
                    var_dump($color, $size, $shopifyVariant['option1'],$shopifyVariant['option2'], $shopifyVariant);
                    exit;
                }

                $newkey .="_".$color->id."_".$size->id;

                $newShopifyVarant = [];

                $newShopifyVarant['id'] = $shopifyVariant['id'];
                $newShopifyVarant['price'] = $shopifyVariant['price'];
                $newShopifyVarant['title'] = $shopifyVariant['title'];
                $newShopifyVarant['weight'] = isset($shopifyVariant['weight']) ? $shopifyVariant['weight'] : 0;
                $newShopifyVarant['sku'] = $shopifyVariant['sku'];
                $newShopifyVarant['option1'] = $option1=="color" ?  $shopifyVariant['option1'] : $shopifyVariant['option2'];
                $newShopifyVarant['option2'] = $option2=="size" ? $shopifyVariant['option2'] : $shopifyVariant['option1'];
                if(!isset($images_map[$shopifyVariant['image_id']])) {
                    // send message to wecome
                    \Nicelizhi\Shopify\Helpers\Utils::send(config("app.name").' '.$item['product_id']. " sku image not found, please check it ");
                    return $this->error("image not found");
                }
                $newShopifyVarant['image_src'] = $images_map[$shopifyVariant['image_id']];
                $newShopifyVarants[$newkey] = $newShopifyVarant;
                $compare_at_price = $shopifyVariant['compare_at_price'];
            }

            //var_dump($newShopifyVarants);exit;

            /**
             * 
             * variants[440][sku]: 8007538966776-variant-1375-1376
             * variants[440][name]: Variant 1375 1376
             * variants[440][price]: 0.0000
             * variants[440][weight]: 0
             * variants[440][status]: 1
             * variants[440][color]: 1375
             * variants[440][size]: 1376
             * variants[440][inventories][1]: 0
             * 
             * 
             */
            $newVariants = [];
            foreach($variants as $k => $variant) {
                //Log::info(json_encode($variant));
                //var_dump($variant);
                $newkey = $item['product_id']."_".$variant['color']."_".$variant['size'];
                if($variant['size']=='1403') {
                    //var_dump($variant);exit;
                }
                $this->info($newkey);
                if(!isset($newShopifyVarants[$newkey])) continue;
                //var_dump($newkey);exit;
                $newVariant['sku'] = $item['product_id'].'-'.$newShopifyVarants[$newkey]['id'];
                $newVariant['name'] = $newShopifyVarants[$newkey]['title'];
                $newVariant['price'] = $newShopifyVarants[$newkey]['price'];
                $newVariant['weight'] = "1000";
                $newVariant['status'] = 1;
                $newVariant['color'] = $variant['color'];
                $newVariant['size'] = $variant['size'];
                $newVariant['inventories'][1] = 1000;
                $categories[] = 5;
                $newVariant['categories'] = $categories;
                $newVariant['guest_checkout'] = 1;
                $newVariant['compare_at_price'] = $compare_at_price;
                $newVariants[$variant['id']] = $newVariant;
            }

            //var_dump(count($newVariants));exit;

            $updateData['variants'] = $newVariants;

            // while method  eq update
            if($method=="update") {
                $newVariants = []; 
                $i = 1;
                $variant_images = [];
                foreach($shopifyVariants as $key=>$shopifyVariant) {
                    $variant_images[$shopifyVariant['id']] = $shopifyVariant['image_id'];
                    //var_dump($shopifyVariant);
                    $newVariant = [];
                    $newVariant['sku'] = $item['product_id'].'-'.$shopifyVariant['id'];
                    $newVariant['name'] = $shopifyVariant['title']; 
                    $newVariant['price'] = $shopifyVariant['price'];
                    $newVariant['weight'] = "1000";
                    $newVariant['status'] = 1;
                    //$attr_option = AttributeOption::where("attribute_id", 23)->where("admin_name", $value)->first();

                    $newVariant['color'] = $this->getAttr($LocalOptions['color_mapp'], $shopifyVariant['option1'], $shopifyVariant['option2'], $shopifyVariant['option3']);
                    $newVariant['size'] = $this->getAttr($LocalOptions['size_mapp'], $shopifyVariant['option1'], $shopifyVariant['option2'], $shopifyVariant['option3']);

                    

                    $newVariant['inventories'][1] = 1000;
                    $categories = [];
                    $categories[] = 5;
                    //$newVariant['categories'] = $categories;
                    //$newVariant['guest_checkout'] = 1;
                    //$newVariant['compare_at_price'] = $shopifyVariant['compare_at_price'];

                    //base use sku for the variant check

                    $variant = $this->productRepository->where("sku", $item['product_id'].'-'.$shopifyVariant['id'])->select(['id'])->first();

                    //need update the product size

                    if(is_null($variant)) {
                        $this->error($shopifyVariant['title'].'--'.$newVariant['color'].'--'.$newVariant['size'].'--variant_'.$i);
                        $newVariants["variant_".$i] = $newVariant;
                    }else{
                        $var_product = [];

                       // $var_product['new'] = 1;
                       // $var_product['featured'] = 1;
                        $var_product['visible_individually'] = 1;
                        $var_product['name'] = $shopifyVariant['title']; 
                        $var_product['status'] = 1;
                        $var_product['guest_checkout'] = 1;
                        $var_product['channel'] = $this->channel;
                        $var_product['locale'] = $this->lang;
                        $var_product[] = $this->category_id;
                        $var_product['categories'] = $categories;
                        $var_product['color'] = $this->getAttr($LocalOptions['color_mapp'], $shopifyVariant['option1'], $shopifyVariant['option2'], $shopifyVariant['option3']);
                        $var_product['size'] = $this->getAttr($LocalOptions['size_mapp'], $shopifyVariant['option1'], $shopifyVariant['option2'], $shopifyVariant['option3']);

                        Event::dispatch('catalog.product.update.before', $variant->id);
                        $var_product = $this->productRepository->update($var_product, $variant->id);

                        Event::dispatch('catalog.product.update.after', $var_product);



                        $this->error($shopifyVariant['title'].'--'.$newVariant['color'].'--'.$newVariant['size'].'--'.$variant->id);
                        $newVariants[$variant->id] = $newVariant;
                    }
                    $i++;
                }

                $updateData['variants'] = $newVariants;
                //var_dump($updateData['variants']);
            }
            /**
             * 
             * 
             * 
             * images[files][32]: 
             * images[files][]: （二进制）
             * 
             * 
             */

             $arrContextOptions=array(
                "ssl"=>array(
                      "verify_peer"=>false,
                      "verify_peer_name"=>false,
                  ),
              ); 
            $images = [];
            foreach($shopifyImages as $key=>$shopifyImage) {

                $info = pathinfo($shopifyImage['src']);


                $this->info($info['filename']);
                $image_path = "product/".$id."/".$info['filename'].".webp";
                $local_image_path = "storage/".$image_path;
                $this->info(public_path($local_image_path));
                if(!file_exists(public_path($local_image_path))) {
                    $this->error("copy [ ".$local_image_path);
                    $this->info($shopifyImage['src']);
                    $contents = file_get_contents($shopifyImage['src'], false, stream_context_create($arrContextOptions));
                    //var_dump($contents);
                    Storage::disk("images")->put($local_image_path, $contents);
                    sleep(1);
                }
                $images[] = $image_path;
            }

            //var_dump($images);exit;

            Event::dispatch('catalog.product.update.before', $id);
            $product = $this->productRepository->update($updateData, $id);
            Event::dispatch('catalog.product.update.after', $product);

            //Log::info(json_encode($updateData));

            //var_dump($updateData, $id);exit;

            //更新对应的分类
            $sku_products = $this->productRepository->where("parent_id", $id)->get();
            foreach($sku_products as $key=>$sku) {

                $sku_image = explode("-", $sku->sku);
                $this->info("process ".$sku->id);

                Event::dispatch('catalog.product.create.after', $sku);

                $updateData = [];

                $updateData['new'] = 1;
                $updateData['featured'] = 1;
                $updateData['visible_individually'] = 1;
                $updateData['status'] = 1;
                $updateData['guest_checkout'] = 1;
                $updateData['channel'] = $this->channel;
                $updateData['locale'] = $this->lang;
                $categories[] = $this->category_id;
                $updateData['categories'] = $categories;

                $this->productRepository->update($updateData, $sku->id);

                Event::dispatch('catalog.product.update.after', $sku);

                $images = [];
                $shopifyImages = [];
                $shopifyImages[] = ['src'=> $images_map[$sku_image[1]] ];
                foreach($shopifyImages as $key=>$shopifyImage) {

                    $this->error($sku->id);
                    $this->error($shopifyImage['src']);

                    //var_dump($shopifyImage);
                    $info = pathinfo($shopifyImage['src']);
    
                    //var_dump($shopifyImage);
    
                    $this->info($info['filename']);
    
                    $image_path = "product/".$sku->id."/".$info['filename'].".webp";
                    $local_image_path = "storage/".$image_path;
                    $this->info(public_path($local_image_path));
                    if(!file_exists(public_path($local_image_path))) {
                        $this->error("copy [ ".$local_image_path);
                        $this->info($shopifyImage['src']);
                        $contents = file_get_contents($shopifyImage['src'], false, stream_context_create($arrContextOptions));
                        Storage::disk("images")->put($local_image_path, $contents);
                        sleep(1);
                    }
                    $images[] = $image_path;
                }
                $max_image_count = 3;
                $i = 0;
                foreach($images as $key=>$image) {
                    $i++;
                    if($max_image_count < $i) continue;
                    $checkImg = ProductImage::where("product_id", $sku->id)->where("path", $image)->first();
                    if(is_null($checkImg)) {
                        $checkImg = new ProductImage();
                        $checkImg->product_id = $sku->id;
                        $checkImg->path = $image;
                        $checkImg->type = "images";
                        $checkImg->save();
                    }
                }



            }


            // exit;
            Cache::pull("sync_".$item['product_id']);

            \Nicelizhi\Shopify\Helpers\Utils::clearCache($id, $item['product_id']); // clear cache

            //send message to wecome
            \Nicelizhi\Shopify\Helpers\Utils::send(config("app.name").' '.$item['product_id']. " sync done, please check it ");



        }


    }


    function getSrcById($images, $targetId) {
        foreach ($images as $image) {
            if ($image['id'] == $targetId) {
                return $image['src'];
            }
        }
        return null; // 如果未匹配到返回 null
    }

   

    public function shopify(Request $request) {
        $req = $request->all();
        //$req['product']['id'] = time(); // for test 
        $input = [];
        $input['sku'] = $req['product']['id'];
        $input['type'] = 'configurable';
        $super_attributes = [];
        $super_attributes_label = []; // super attributes label
        $super_attributes_ids = [];

        // create a family attribute by sku
        $attributeFamilyRepository = app('Webkul\Attribute\Repositories\AttributeFamilyRepository');

        //check if the attribute family is exist
        $attributeFamily = $attributeFamilyRepository->findOneByField('code', $input['sku']);



        $attribute_group_id = 0;
        $action = 'create';

        if(!$attributeFamily){
            Event::dispatch('catalog.attribute_family.create.before');
            $attributeFamily = $attributeFamilyRepository->create([
                'code' =>$input['sku'],
                'name' => $input['sku'],
                'status' => 1,
                'is_user_defined' => 1
            ]);

            Event::dispatch('catalog.attribute_family.create.after', $attributeFamily);
            // create a default group for the family
            $attributeGroupRepository = app('Webkul\Attribute\Repositories\AttributeGroupRepository');
            Event::dispatch('catalog.attributeGroup.create.before');
            $attributeGroupData = [
                'name' => 'General',
                'position' => 1,
                'attribute_family_id' => $attributeFamily->id
            ];

            $attributeGroup = $attributeFamily->attribute_groups()->create($attributeGroupData);

            Event::dispatch('catalog.attributeGroup.create.before', $attributeGroup);

            // base use attribute group add attribute group mappings
            //$attributeGroupMappingRepository = app('Webkul\Attribute\Repositories\AttributeGroupMappingRepository');
            $attributeGroupItems = $attributeGroupRepository->where('attribute_family_id', $attributeFamily->id)->limit(1)->get();

            //var_dump($attributeGroupItems);exit;
            
            foreach($attributeGroupItems as $attributeGroupItem) {
                $attributeGroupMapping = DB::table('attribute_group_mappings')->where("attribute_id", )->where("attribute_group_id", $attributeGroupItem->id)->first();
                if(!$attributeGroupMapping){
                    $attributeMaxID = 32;
                    $attributeGroupMappingDatas = [];
                    for($i=1;$i<=$attributeMaxID;$i++){

                        // check if the attribute group mapping is have the attribute
                        $attributeGroupMappingData = [
                            'attribute_id' => $i,
                            'attribute_group_id' => $attributeGroupItem->id
                        ];

                        $attributeGroupMappingDatas[] = $attributeGroupMappingData;
                    }
                    DB::table('attribute_group_mappings')->insert($attributeGroupMappingDatas);
                    
                }
                $attribute_group_id = $attributeGroupItem->id;
            }
        }else{
            $attributeGroupRepository = app('Webkul\Attribute\Repositories\AttributeGroupRepository');
            $attributeGroup = $attributeGroupRepository->findOneByField('attribute_family_id', $attributeFamily->id);
            $attribute_group_id = $attributeGroup->id;
        }
        $input['attribute_family_id'] = $attributeFamily->id;

        // create super attributes and check if the attribute is valid
        $attributeRepository = app('Webkul\Attribute\Repositories\AttributeRepository');


        // print_r($req['product']['options']);exit;

        foreach ($req['product']['options'] as $attribute) {
            //var_dump($attribute['values']);

            $code = "attr_".$input['attribute_family_id']."_".md5($attribute['name']);
            $super_attributes_label[$attribute['position']] = $code;

            // create a unique code for the attribute
            //findOneByField 基于某个字段查找
            $attributeRepos = $attributeRepository->findOneByField('code', $code);
            //  var_dump($code);exit;
            if(!$attributeRepos){
                // attribute not found and create a new attribute
                Event::dispatch('catalog.attribute.create.before');
                $attributeRepos = $attributeRepository->create([
                    'code' => $code,
                    'admin_name' => $attribute['name'],
                    'type' => 'select',
                    'is_required' => 0,
                    'is_unique' => 0,
                    'validation' => '',
                    'position' => $attribute['position'],
                    'is_visible' => 1,
                    'is_configurable' => 1,
                    'is_filterable' => 1,
                    'use_in_flat' => 0,
                    'is_comparable' => 0,
                    'is_visible_on_front' => 0,
                    'swatch_type' => 'dropdown',
                    'use_in_product_listing' => 1,
                    'use_in_comparison' => 1,
                    'is_user_defined' => 1,
                    'value_per_locale' => 0,
                    'value_per_channel' => 0,
                    'channel_based' => 0,
                    'locale_based' => 0,
                    'default_value' => ''
                ]);
                Event::dispatch('catalog.attribute.create.after', $attribute);
            }
            // check if the attribute option is valid
            $attributeOptionRepository = app('Webkul\Attribute\Repositories\AttributeOptionRepository');
            $attributeOptionArray = [];
            foreach ($attribute['values'] as $option) {
                $attributeOption = $attributeOptionRepository->findOneByField(['admin_name'=>$option, 'attribute_id'=>$attributeRepos->id]);
                if(!$attributeOption){
                    $attributeOption = $attributeOptionRepository->create([
                        'admin_name' => $option,
                        'sort_order' => $attribute['position'],
                        'attribute_id' => $attributeRepos->id
                    ]);
                }
                //var_dump($attributeOption->admin_name);
                $attributeOptionArray[$attributeOption->id] = $attributeOption->id;

                //Log::info('Attribute Option: ' . json_encode($attributeOption));
            }
            $super_attributes[$attributeRepos->code] = $attributeOptionArray;
            $super_attributes_ids[$attributeRepos->id] = $attributeRepos->id;
        }

        $input['super_attributes'] = $super_attributes;
        $input['channel'] = Core()->getCurrentChannel()->code;
        $input['locale'] = Core()->getCurrentLocale()->code;


        //add attribut id to attribute_group_mappings
        $attributeGroupMappingRespos = app();
        foreach($super_attributes_ids as $key=>$super_attributes_id) {
            $attribute_group_mapping = DB::table('attribute_group_mappings')->where("attribute_id", $super_attributes_id)->where("attribute_group_id", $attribute_group_id)->first();
            if(!$attribute_group_mapping){
                DB::table('attribute_group_mappings')->insert([
                    'attribute_id' => $super_attributes_id,
                    'attribute_group_id' => $attribute_group_id
                ]);
            }
        }


        //findBySlug 基于url-key查找产品
        $product = $this->productRepository->findBySlug($req['product']['handle']);

        if(!is_null($product)) {
            $id = $product->id;
            $action = 'update';

        }else{
            Event::dispatch('catalog.product.create.before');

            $product = $this->productRepository->create($input);
            Event::dispatch('catalog.product.create.after', $product);
            $id = $product->id;
        }

        $multiselectAttributeCodes = [];
        $productAttributes = $this->productRepository->findOrFail($id);

        Event::dispatch('catalog.product.update.before', $id);

        $tableData = [];
        $skus = $request->input('variants');

        $categories = $request->input('categories');
        $categories[] = 5; // add the default category

        $Variants = [];
        $VariantsImages = [];

        $variantCollection = $product->variants()->get()->toArray(); // get the variants of the product
        $variantCollection = array_column($variantCollection, null, 'sku');

        if($action =='create') {
            $product->variants()->delete(); // delete the variants of the product
        }

        // match the variants to the sku id
        $skus = $req['product']['variants'];

        $i = 0;
        foreach($skus as $key=>$sku) {
            $Variant = [];
            if(empty($sku['sku'])) continue;
            $sku['sku'] = !empty($sku['sku']) ? $sku['sku'] :'';
            
            // $title = "";
            // if(!empty($sku['option1'])) $title .= $sku['option1'];
            // if(!empty($sku['option2'])) $title .= "-".$sku['option2'];
            // if(!empty($sku['option3'])) $title .= "-".$sku['option3'];

            $Variant['name'] = $sku['title'];
            $Variant['price'] = $sku['price'];
            $Variant['weight'] = "1000";
            $Variant['status'] = 1;
            $Variant['inventories'][1] = 1000;
            $Variant['channel'] = Core()->getCurrentChannel()->code;
            $Variant['locale'] = Core()->getCurrentLocale()->code;
            $Variant['visible_individually'] = 1;
            $Variant['guest_checkout'] = 1;
            $Variant['new'] = 1;
            $Variant['attribute_family_id'] = $attributeFamily->id;
            $Variant['manage_stock'] = 0;
            $Variant['visible_individually'] = 1;
            $Variant['product_number'] = 10000;


            $Variant['categories'] = $categories;
            $option1 = isset($super_attributes_label[1]) ? $super_attributes_label[1] : null;
            $option2 = isset($super_attributes_label[2]) ? $super_attributes_label[2] : null;
            $option3 = isset($super_attributes_label[3]) ? $super_attributes_label[3] : null;

            //if($option1) $Variant[$option1] = $sku['option1'];
            if($option1) $Variant[$option1] = $this->findAttributeOptionID($option1, $sku['option1']);
            if($option2) $Variant[$option2] = $this->findAttributeOptionID($option2, $sku['option2']);
            if($option3) $Variant[$option3] = $this->findAttributeOptionID($option3, $sku['option3']);
            
            
            $Variant['sku'] = $input['sku'].'-'. $sku['sku'];
            $Variants["variant_".$i] = $Variant;
            $i++;
        }

        $tableData['channel'] = Core()->getCurrentChannel()->code;
        $tableData['locale'] = Core()->getCurrentLocale()->code;
        $tableData['variants'] = $Variants;
        $tableData['url_key'] = isset($req['product']['handle']) ? $req['product']['handle'] : '';
        $tableData['name'] = $req['product']['title'];
        $tableData['new'] = 1;
        $tableData['guest_checkout'] = 1;
        $tableData['status'] = 1;
        $tableData['description'] = $req['product']['body_html'];
        $tableData['price'] = $req['product']['variants'][0]['price'];
        $tableData['compare_at_price'] = $req['product']['variants'][0]['compare_at_price'];
        $tableData['visible_individually'] = 1;
        $tableData['manage_stock'] = 0;
        $tableData['inventories'][1] = 1000;
        $tableData['product_number'] = 10000;

        //Log::info("quick-create-product: ".json_encode($tableData));


        // var_dump($id);

        // print_r($tableData);exit;

        //var_dump($tableData, $id);exit;

        $product = $this->productRepository->update($tableData, $id);
        Event::dispatch('catalog.product.update.after', $product);

        // $images = $request->input('images');


        $images = [];
        foreach($req['product']['variants'] as $m=>$n) {

            if(!empty($n['image_id'])){
                $images[$m]['url'] = $this->getAttr($id, $req,$n['image_id']);
            }


        }
    

        $productImages = [];
        $uniquePaths = []; // 用于存储已处理过的 path 值
        
        foreach ($images as $key => $image) {
            $path = env('APP_URL') . '/storage/' . $image['url'];
        
            // 如果 path 不在已处理列表中，则加入结果数组
            if (!in_array($path, $uniquePaths, true)) {
                $uniquePaths[] = $path; // 记录已处理的 path
                $productImages[] = [
                    'path' => $path,
                    'type' => 'images',
                    'position' => $key
                ];
            }
        }
        
        // 输出去重后的数组
        print_r($productImages);

        $product->images()->createMany($productImages);

        $data = [];

        $data['error'] = 1;

        return response()->json($productImages);
    }


// 根据字段去重
protected function arrayUniqueByField($array, $field) {
    $uniqueValues = []; // 存储已出现的字段值
    $result = [];       // 存储去重后的数组

    foreach ($array as $item) {
        if (!in_array($item[$field], $uniqueValues)) {
            $uniqueValues[] = $item[$field]; // 记录已出现的字段值
            $result[] = $item;               // 将当前项加入结果
        }
    }

    return $result;
}

    protected function getAttr($product_id, $req,$image_id) {

        $arrContextOptions=array(
            "ssl"=>array(
                  "verify_peer"=>false,
                  "verify_peer_name"=>false,
              ),
          ); 
        
        
          $images_url = $this->getSrcById($req['product']['images'], $image_id);
          $info = pathinfo($images_url);
        
    
         $image_path = "product/".$product_id."/".$info['filename'].".webp";
         $local_image_path = "storage/".$image_path;
         
         if(!file_exists(public_path($local_image_path))) {
            
             $req['product']['image']['src'];
             $contents = file_get_contents($images_url, false, stream_context_create($arrContextOptions));
             //var_dump($contents);  Storage
             Storage::disk("images")->put($local_image_path, $contents);
        
         }
         $images = $image_path;
        return $images;
    }


    public function findAttributeOptionID($attribute_id, $attribute_value) {

        //
        $attributeRepository = app('Webkul\Attribute\Repositories\AttributeRepository');
    
        $attribute = $attributeRepository->findOneByField('code', $attribute_id);
        if(!$attribute) return 0;
    
        Log::info("findAttributeOptionID: ".$attribute->id." ".$attribute_value);
    
        //
        $attributeOptionRepository = app('Webkul\Attribute\Repositories\AttributeOptionRepository');
        $attributeOption = $attributeOptionRepository->findOneByField(['admin_name'=>$attribute_value, 'attribute_id'=>$attribute->id]);
        if($attributeOption){
            return $attributeOption->id;
        }
        return 0;
    }

    // shopify images save
    public function shopifyImages(Request $request) {
        $req = $request->all();

        Log::info("shopifyImages: ".json_encode($req));

        // match the variants to the sku id
        $skus = $req['product']['variants'];
        $main_sku = $req['product']['id'];

        //product images
        $images = [];
        foreach($req['product']['images'] as $key=>$image) {
            $images[$image['id']] = $image['src'];
        }

        $arrContextOptions=array(
            "ssl"=>array(
                  "verify_peer"=>false,
                  "verify_peer_name"=>false,
              ),
        ); 

        // the images need insert the main products
        $product = $this->productRepository->findOneByField("sku", $main_sku);
        
        if(empty($product)) return false;

        $productImages = [];
        foreach($images as $key=>$image) {
            $product_id = $product->id;
            $images_url = $image;
            $info = pathinfo($images_url);
            $image_path = "product/".$product_id."/".$info['filename'].".webp";
            $local_image_path = "storage/".$image_path;
            if(!file_exists(public_path($local_image_path))) {
            
                $contents = file_get_contents($images_url, false, stream_context_create($arrContextOptions));
                Storage::disk("images")->put($local_image_path, $contents);
            }
            
            $productImages[] = [
                'path' => env('APP_URL') . '/storage/'.$image_path,
                'type' => 'images',
                'position' => 1
            ];
        }

        $product->images()->createMany($productImages);


        foreach($skus as $key=>$sku) {
            if(empty($sku['sku'])) continue;
            // use the sku to find the product id and add the images to the sku
            $sku_code = $main_sku."-".$sku['sku'];
            $product = $this->productRepository->findOneByField("sku", $sku_code);
            if(is_null($product)) continue;
            $product_id = $product->id;
            $images_url = isset($images[$sku['image_id']]) ? trim($images[$sku['image_id']]) : null;
            if(is_null($images_url)) continue;
            $info = pathinfo($images_url);
            $image_path = "product/".$product_id."/".$info['filename'].".webp";
            $local_image_path = "storage/".$image_path;
            if(!file_exists(public_path($local_image_path))) {
            
                $contents = file_get_contents($images_url, false, stream_context_create($arrContextOptions));
                //var_dump($contents);  Storage
                Storage::disk("images")->put($local_image_path, $contents);
           
            }

            $productImages = [];
            $productImages[] = [
                'path' => env('APP_URL') . '/storage/'.$image_path,
                'type' => 'images',
                'position' => 1
            ];


            $product->images()->createMany($productImages);

        }


    }

}



