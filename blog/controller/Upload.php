<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/8/27
 * Time: 20:58
 */
namespace blog\controller;
use blog\lib\Web;

class Upload extends Web
{

//    nginx 文件上传

    /**
     * @return array
     * location /upload {
    # 转到后台处理URL,表示Nginx接收完上传的文件后，然后交给后端处理的地址
    upload_pass /blog/upload;
    # 临时保存路径, 可以使用散列
    # 上传模块接收到的文件临时存放的路径， 1 表示方式，该方式是需要在/tmp/nginx_upload下创建以0到9为目录名称的目录，上传时候会进行一个散列处理。
    upload_store /tmp/nginx_upload;
    # 上传文件的权限，rw表示读写 r只读
    upload_store_access user:rw group:rw all:rw;
    # upload_resumable on;
    set $ngf 'rxwyun_102410_ngf';
    # 这里写入http报头，pass到后台页面后能获取这里set的报头字段
    upload_set_form_field $ngf.$upload_field_name[name] $upload_file_name;
    upload_set_form_field $ngf.$upload_field_name[content_type] $upload_content_type;
    upload_set_form_field $ngf.$upload_field_name[path] $upload_tmp_path;
    # Upload模块自动生成的一些信息，如文件大小与文件md5值
    upload_aggregate_form_field $ngf.$upload_field_name[md5] $upload_file_md5;
    upload_aggregate_form_field $ngf.$upload_field_name[size] $upload_file_size;
    # 允许的字段，允许全部可以 "^.*$"
    upload_pass_form_field "^.*$";
    # upload_pass_form_field "^submit$|^description$";
    # 每秒字节速度控制，0表示不受控制，默认0, 128K
    upload_limit_rate 0;
    # 如果pass页面是以下状态码，就删除此次上传的临时文件
    upload_cleanup 400 404 499 500-505;
    # 打开开关，意思就是把前端脚本请求的参数会传给后端的脚本语言，比如：http://192.168.1.251:9000/upload/?k=23,后台可以通过POST['k']来访问。
    upload_pass_args on;
    }
    location /blog/upload {
    proxy_pass http://127.0.0.1:86;
    # return 200;  # 如果不需要后端程序处理，直接返回200即可
    }
     */
    public function indexApi ()
    {
//        这里会把上传的参数也获取到  也是post方式，  这样的话就算需要token或者其他的验证也可以通过
        $path = $this->upload->save($this->request->post());
        if ($path) {
            return [200, $path];
        }
        return [501,'上传失败'];
//        if ($path) {
//            $this->getComponent($this->getSystem(),'imgzip')->resize($path, 600, 300);
//            return ['ret' => 200, 'data' => $path];
//        }
    }
}