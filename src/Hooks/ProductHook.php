<?php

namespace Ras\Hooks;

use Ras\Exceptions\RasBlockNotFoundException;
use Ras\Utilities\ProductUtil;
use WP_REST_Request;
use WP_REST_Response;

class ProductHook
{

    /**
     * @param WP_REST_Request $request
     * @return void|WP_REST_Response
     */
    public function ras_get_product_html(WP_REST_Request $request)
    {
        $product_id = $request->get_param('product_id');
        $view = $request->get_param('view');

        $productUtil = new ProductUtil($product_id);
        if (!$productUtil->is_product_in_stock()) {
            return new WP_REST_Response(
                'Product not found',
                '404'
            );
        }

        try {
            $productUtil->get_product_html($view);
        } catch (RasBlockNotFoundException $exception) {
            return new WP_REST_Response(
                $exception->getMessage(),
                $exception->getCode()
            );
        }

    }


}