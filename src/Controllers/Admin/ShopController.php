<?php

namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Models\{
    Shop,
    Bought
};
use App\Utils\DatatablesHelper;
use Slim\Http\{
    Request,
    Response
};
use Ozdemir\Datatables\Datatables;
use Psr\Http\Message\ResponseInterface;

class ShopController extends AdminController
{
    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function index($request, $response, $args): ResponseInterface
    {
        $table_config['total_column'] = array(
            'op' => '操作', 'id' => 'ID', 'name' => '商品名称',
            'price' => '价格', 'content' => '商品内容',
            'auto_renew' => '自动续费', 'auto_reset_bandwidth' => '续费时是否重置流量',
            'status' => '状态', 'period_sales' => '周期销量'
        );
        $table_config['default_show_column'] = array();
        foreach ($table_config['total_column'] as $column => $value) {
            $table_config['default_show_column'][] = $column;
        }
        $table_config['ajax_url'] = 'shop/ajax';
        return $response->write(
            $this->view()
                ->assign('table_config', $table_config)
                ->display('admin/shop/index.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function create($request, $response, $args): ResponseInterface
    {
        return $response->write(
            $this->view()
                ->display('admin/shop/create.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function add($request, $response, $args): ResponseInterface
    {
        $shop = new Shop();
        $shop->name = $request->getParam('name');
        $shop->price = $request->getParam('price');
        $shop->auto_renew = $request->getParam('auto_renew');
        $shop->auto_reset_bandwidth = $request->getParam('auto_reset_bandwidth');

        $content = array();
        if ($request->getParam('bandwidth') != 0) {
            $content['bandwidth'] = $request->getParam('bandwidth');
        }

        if ($request->getParam('expire') != 0) {
            $content['expire'] = $request->getParam('expire');
        }

        if ($request->getParam('class') != 0) {
            $content['class'] = $request->getParam('class');
        }

        if ($request->getParam('class_expire') != 0) {
            $content['class_expire'] = $request->getParam('class_expire');
        }

        if ($request->getParam('reset') != 0) {
            $content['reset'] = $request->getParam('reset');
        }

        if ($request->getParam('reset_value') != 0) {
            $content['reset_value'] = $request->getParam('reset_value');
        }

        if ($request->getParam('reset_exp') != 0) {
            $content['reset_exp'] = $request->getParam('reset_exp');
        }

        //if ($request->getParam('speedlimit')!=0) {
        $content['speedlimit'] = $request->getParam('speedlimit');
        //}

        //if ($request->getParam('connector')!=0) {
        $content['connector'] = $request->getParam('connector');
        //}

        if ($request->getParam('content_extra') != '') {
            $content['content_extra'] = $request->getParam('content_extra');
        }

        $shop->content = $content;

        if (!$shop->save()) {
            $rs['ret'] = 0;
            $rs['msg'] = '添加失败';
        } else {
            $rs['ret'] = 1;
            $rs['msg'] = '添加成功';
        }

        return $response->withJson(
            $rs
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function edit($request, $response, $args): ResponseInterface
    {
        $id = $args['id'];
        $shop = Shop::find($id);
        return $response->write(
            $this->view()
                ->assign('shop', $shop)
                ->display('admin/shop/edit.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function update($request, $response, $args): ResponseInterface
    {
        $id = $args['id'];
        $shop = Shop::find($id);

        $shop->name = $request->getParam('name');
        $shop->price = $request->getParam('price');
        $shop->auto_renew = $request->getParam('auto_renew');

        if ($shop->auto_reset_bandwidth == 1 && $request->getParam('auto_reset_bandwidth') == 0) {
            $boughts = Bought::where('shopid', $id)->get();

            foreach ($boughts as $bought) {
                $bought->renew = 0;
                $bought->save();
            }
        }

        $shop->auto_reset_bandwidth = $request->getParam('auto_reset_bandwidth');
        $shop->status = 1;

        $content = array();
        if ($request->getParam('bandwidth') != 0) {
            $content['bandwidth'] = $request->getParam('bandwidth');
        }

        if ($request->getParam('expire') != 0) {
            $content['expire'] = $request->getParam('expire');
        }

        if ($request->getParam('class') != 0) {
            $content['class'] = $request->getParam('class');
        }

        if ($request->getParam('class_expire') != 0) {
            $content['class_expire'] = $request->getParam('class_expire');
        }

        if ($request->getParam('reset') != 0) {
            $content['reset'] = $request->getParam('reset');
        }

        if ($request->getParam('reset_value') != 0) {
            $content['reset_value'] = $request->getParam('reset_value');
        }

        if ($request->getParam('reset_exp') != 0) {
            $content['reset_exp'] = $request->getParam('reset_exp');
        }

        //if ($request->getParam('speedlimit')!=0) {
        $content['speedlimit'] = $request->getParam('speedlimit');
        //}

        //if ($request->getParam('connector')!=0) {
        $content['connector'] = $request->getParam('connector');
        //}

        if ($request->getParam('content_extra') != '') {
            $content['content_extra'] = $request->getParam('content_extra');
        }

        $shop->content = $content;

        if (!$shop->save()) {
            $rs['ret'] = 0;
            $rs['msg'] = '保存失败';
        } else {
            $rs['ret'] = 1;
            $rs['msg'] = '保存成功';
        }

        return $response->withJson(
            $rs
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function deleteGet($request, $response, $args): ResponseInterface
    {
        $id = $request->getParam('id');
        $shop = Shop::find($id);
        $shop->status = 0;
        if (!$shop->save()) {
            $rs['ret'] = 0;
            $rs['msg'] = '下架失败';
            return $response->withJson(
                $rs
            );
        }

        $boughts = Bought::where('shopid', $id)->get();

        foreach ($boughts as $bought) {
            $bought->renew = 0;
            $bought->save();
        }

        $rs['ret'] = 1;
        $rs['msg'] = '下架成功';

        return $response->withJson(
            $rs
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function bought($request, $response, $args): ResponseInterface
    {
        $table_config['total_column'] = array(
            'op' => '操作', 'id' => 'ID',
            'datetime' => '购买日期', 'content' => '内容',
            'price' => '价格', 'user_id' => '用户ID',
            'user_name' => '用户名', 'renew' => '自动续费时间',
            'auto_reset_bandwidth' => '续费时是否重置流量'
        );
        $table_config['default_show_column'] = array();
        foreach ($table_config['total_column'] as $column => $value) {
            $table_config['default_show_column'][] = $column;
        }
        $table_config['ajax_url'] = 'bought/ajax';
        return $response->write(
            $this->view()
                ->assign('table_config', $table_config)
                ->display('admin/shop/bought.tpl')
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function deleteBoughtGet($request, $response, $args): ResponseInterface
    {
        $id = $request->getParam('id');
        $shop = Bought::find($id);
        $shop->renew = 0;
        if (!$shop->save()) {
            $rs['ret'] = 0;
            $rs['msg'] = '退订失败';
            return $response->write(
                $response->getBody()->write(json_encode($rs))
            );
        }
        $rs['ret'] = 1;
        $rs['msg'] = '退订成功';
        return $response->withJson(
            $rs
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function ajax_shop($request, $response, $args): ResponseInterface
    {
        $datatables = new Datatables(new DatatablesHelper());
        $datatables->query('Select id as op,id,name,price,content,auto_renew,auto_reset_bandwidth,status,id as period_sales from shop');

        $datatables->edit('op', static function ($data) {
            return '<a class="btn btn-brand" href="/admin/shop/' . $data['id'] . '/edit">编辑</a>
                    <a class="btn btn-brand-accent" ' . ($data['status'] == 0 ? 'disabled' : 'id="row_delete_' . $data['id'] . '" href="javascript:void(0);" onClick="delete_modal_show(\'' . $data['id'] . '\')"') . '>下架</a>';
        });

        $datatables->edit('content', static function ($data) {
            $shop = Shop::find($data['id']);
            return $shop->content();
        });

        $datatables->edit('auto_renew', static function ($data) {
            if ($data['auto_renew'] == 0) {
                return '不自动续费';
            }

            return $data['auto_renew'] . ' 天后续费';
        });

        $datatables->edit('auto_reset_bandwidth', static function ($data) {
            return $data['auto_reset_bandwidth'] == 0 ? '不自动重置' : '自动重置';
        });

        $datatables->edit('status', static function ($data) {
            return $data['status'] == 1 ? '上架' : '下架';
        });

        $datatables->edit('period_sales', static function ($data) {
            $shop = Shop::find($data['id']);
            $period = $_ENV['sales_period'];

            if ($period == 'expire') {
                $period = json_decode($shop->content, true)['class_expire'];
            }

            $period = $period * 24 * 60 * 60;
            $sales = Bought::where('shopid', $shop->id)->where('datetime', '>', time() - $period)->count();
            return $sales;
        });

        return $response->write(
            $datatables->generate()
        );
    }

    /**
     * @param Request   $request
     * @param Response  $response
     * @param array     $args
     */
    public function ajax_bought($request, $response, $args): ResponseInterface
    {
        $datatables = new Datatables(new DatatablesHelper());
        $datatables->query('Select bought.id as op,bought.id as id,bought.datetime,shop.id as content,bought.price,user.id as user_id,user.user_name,renew,shop.auto_reset_bandwidth from bought,user,shop where bought.shopid = shop.id and bought.userid = user.id');

        $datatables->edit('op', static function ($data) {
            return '<a class="btn btn-brand-accent" ' . ($data['renew'] == 0 ? 'disabled' : ' id="row_delete_' . $data['id'] . '" href="javascript:void(0);" onClick="delete_modal_show(\'' . $data['id'] . '\')"') . '>中止</a>';
        });

        $datatables->edit('content', static function ($data) {
            $shop = Shop::find($data['content']);
            return $shop->content();
        });

        $datatables->edit('renew', static function ($data) {
            if ($data['renew'] == 0) {
                return '不自动续费';
            }

            return date('Y-m-d H:i:s', $data['renew']) . ' 续费';
        });

        $datatables->edit('auto_reset_bandwidth', static function ($data) {
            return $data['auto_reset_bandwidth'] == 0 ? '不自动重置' : '自动重置';
        });

        $datatables->edit('datetime', static function ($data) {
            return date('Y-m-d H:i:s', $data['datetime']);
        });

        return $response->write(
            $datatables->generate()
        );
    }
}
