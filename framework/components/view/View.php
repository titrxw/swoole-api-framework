<?php
namespace framework\components\view;

use framework\base\Component;

class View extends Component
{
    protected $_layout = null;
    protected $_viewPath = null;
    protected $_compilePath = null;
    protected $_options = array();
    protected $_cachePath;
    protected $_cacheFile = null;
    protected $_leftDelimiter;
    protected $_rightDelimiter;
    protected $_controller;
    protected $_action;
    protected $_viewExt;
    protected $_cacheExpire;
    protected $_isCache;

    protected function init()
    {
        $this->_viewPath = APP_ROOT.APP_NAME.'/'.$this->getValueFromConf('templatePath','view');
        $this->_cachePath = APP_ROOT.APP_NAME.'/'.$this->getValueFromConf('cachePath','runtime/viewCache');
        $this->_compilePath = APP_ROOT.APP_NAME.'/'.$this->getValueFromConf('compilePath','runtime/compile');
        $this->_viewExt = $this->getValueFromConf('viewExt','.html');
        $this->_isCache = $this->getValueFromConf('isCache',true);
        if ($this->_isCache)
            $this->_cacheExpire = $this->getValueFromConf('cacheExpire',31536000);
        $this->_leftDelimiter = $this->getValueFromConf('leftDelimiter','{');
        $this->_rightDelimiter = $this->getValueFromConf('rightDelimiter','}');
    }

    /**
     * 设置视图文件布局结构的文件名(layout)
     *
     * layout默认为:null
     *
     * @access public
     *
     * @param string $layoutName 所要设置的layout名称
     *
     * @return boolean
     */
    public function setLayout($layoutName = null) {

        $this->_layout = $layoutName;

        return true;
    }

    /**
     * 分析视图缓存文件是否需要重新创建
     *
     * @access public
     *
     * @param string $cacheId 缓存ID
     * @param integer $expire 缓存文件生存周期, 默认为一年
     *
     * @return boolean
     */
    public function cache($cacheId = null, $expire = null)
    {
        if (!$this->_isCache)
        {
            return false;
        }
        //参数分析
        if (!$cacheId) {
            $cacheId = $this->_action;
        }
        if (!$expire) {
            $expire = $this->_cacheExpire;
        }

        //获取视图缓存文件
        $cacheFile = $this->parseCacheFile($cacheId);
        $server = $this->getComponent('url')->getServer();
        if (is_file($cacheFile) && (filemtime($cacheFile) + $expire >= $server['REQUEST_TIME'])) {
            unset($server);
            return $cacheFile;
        }

        unset($server);
        $this->_cacheFile   = $cacheFile;

        return false;
    }

    /**
     * 视图变量赋值操作
     *
     * @access public
     *
     * @param mixed $keys 视图变量名
     * @param mixed $value 视图变量值
     *
     * @return mixed
     */
    public function assign($keys, $value = null) {

        //参数分析
        if (!$keys) {
            return false;
        }

        if (!is_array($keys)) {
            $this->_options[$keys] = $value;
            unset($value);
            return true;
        }

        foreach ($keys as $handle=>$lines) {
            $this->_options[$handle] = $lines;
        }
        unset($keys, $value);

        return true;
    }

    /**
     * 显示当前页面的视图内容
     *
     * 包括视图页面中所含有的挂件(widget), 视图布局结构(layout), 及render()所加载的视图片段等
     *
     * @access public
     *
     * @param string $fileName 视图名称
     *
     * @return string
     */
    public function display($fileName = null) {

        //模板变量赋值
        if ($this->_options) {
            extract($this->_options, EXTR_PREFIX_SAME, 'data');
            $this->_options = array();
        }

        $currentModule = $this->getComponent('url')->getCurrentModule();
        $this->_controller = $currentModule['controller'];
        $this->_action = $currentModule['action'];
        unset($currentModule);

        //加载编译缓存文件
        ob_start();
        $cache = $this->cache($this->_controller.'/'.$this->_action);
        if (!$cache)
        {
            //分析视图文件名
            $fileName    = $this->parseViewName($fileName);

            //获取视图模板文件及编译文件的路径
            $viewFile    = $this->getViewFile($fileName);
            $compileFile = $this->getCompileFile($fileName);

            //分析视图编译文件是否需要重新生成
            if ($this->isCompile($viewFile, $compileFile)) {
                $templateContent = $this->loadViewFile($viewFile);
                //重新生成编译缓存文件
                $this->createCompileFile($compileFile, $templateContent);
            }
            include $compileFile;
        }
        else
        {
            include $cache;
        }
        $viewContent = ob_get_clean();
        if(!$cache)
        {
            $this->createCache($viewContent);
        }

        $this->_options = array();
        return $viewContent;
    }

    /**
     * 加载并显示视图片段文件内容
     *
     * 相当于include 代码片段，当$return为:true时返回代码代码片段内容,反之则显示代码片段内容。注：本方法不支持layout视图
     *
     * @access public
     *
     * @param string $fileName 视图片段文件名称
     * @param array $data 视图模板变量，注：数组型
     * @param boolean $return 是否有返回数据。true:返回数据/false:没有返回数据，默认：false
     *
     * @return string
     */
    public function render($fileName = null, $data = array(), $return = false) {

        //分析视图文件名
        $viewName    = $this->parseViewName($fileName);

        //获取视图模板文件及编译文件的路径
        $viewFile    = $this->getViewFile($viewName);
        $compileFile = $this->getCompileFile($viewName);

        //分析视图编译文件是否需要重新生成
        if ($this->isCompile($viewFile, $compileFile)) {
            $templateContent = $this->loadViewFile($viewFile);
            //重新生成编译缓存文件
            $this->createCompileFile($compileFile, $templateContent);
        }

        //模板变量赋值
        if ($data && is_array($data)) {
            extract($data, EXTR_PREFIX_SAME, 'data');
            unset($data);
        } else {
            //当且仅当本方法在处理action视图(非视图片段)时，对本类assign()所传递的视图变量进行赋值
            if (!$fileName && $this->_options) {
                extract($this->_options, EXTR_PREFIX_SAME, 'data');
                $this->_options = array();
            }
        }

        $viewContent = require_once $compileFile;

        return $viewContent;
    }

    /**
     * 加载并显示视图片段文件内容
     *
     * 用于处理视图标签include的视图内容
     *
     * @access protected
     *
     * @param string $fileName 视图片段文件名称
     *
     * @return string
     */
    protected function addView($fileName)
    {
        //参数分析
        if (!$fileName) {
            return false;
        }

        return $this->render($fileName);
    }

    /**
     * 生成视图编译文件
     *
     * @access protected
     *
     * @param string $compileFile 编译文件名
     * @param string $content    编译文件内容
     *
     * @return void
     */
    protected function createCompileFile($compileFile, $content)
    {
        //分析编译文件目录
        $compileDir = dirname($compileFile);
        if (!is_dir($compileDir)) {
            mkdir($compileDir, 0777, true);
        }
        $content = "<?php " . "?>" . $content ;
        return file_put_contents($compileFile, $content, LOCK_EX);
    }

    /**
     * 加载视图文件
     *
     * 加载视图文件并对视图标签进行编译
     *
     * @access protected
     *
     * @param string $viewFile 视图文件及路径
     *
     * @return string
     */
    protected function loadViewFile($viewFile) {

        //分析视图文件是否存在
        if (!is_file($viewFile)) {
            throw new \Exception("The view file: {$viewFile} is not found!", '404');
        }

        $viewContent = file_get_contents($viewFile);
        //编译视图标签
        return $this->handleViewFile($viewContent);
    }

    /**
     * 编译视图标签
     *
     * @access protected
     *
     * @param string $viewContent 视图(模板)内容
     *
     * @return string
     */
    protected function handleViewFile($viewContent) {

        //参数分析
        if (!$viewContent) {
            return false;
        }

        //正则表达式匹配的模板标签
        $regexArray = array(
            '#'.$this->_leftDelimiter.'\s*include\s+(.+?)\s*'.$this->_rightDelimiter.'#is',
            '#'.$this->_leftDelimiter.'php\s+(.+?)'.$this->_rightDelimiter.'#is',
            '#'.$this->_leftDelimiter.'\s?else\s?'.$this->_rightDelimiter.'#i',
            '#'.$this->_leftDelimiter.'\s?\/if\s?'.$this->_rightDelimiter.'#i',
            '#'.$this->_leftDelimiter.'\s?\/loop\s?'.$this->_rightDelimiter.'#i',
        );

        ///替换直接变量输出
        $replaceArray = array(
            "<?php \$this->addView('\\1'); ?>",
            "<?php \\1 ?>",
            "<?php } else { ?>",
            "<?php } ?>",
            "<?php } } ?>",
        );

        //对固定的视图标签进行编辑
        $viewContent = preg_replace($regexArray, $replaceArray, $viewContent);
        //处理if, loop, 变量等视图标签
        $patternArray = array(
            '#'.$this->_leftDelimiter.'\s*(\$.+?)\s*'.$this->_rightDelimiter.'#i',
            '#'.$this->_leftDelimiter.'\s?(if\s.+?)\s?'.$this->_rightDelimiter.'#i',
            '#'.$this->_leftDelimiter.'\s?(elseif\s.+?)\s?'.$this->_rightDelimiter.'#i',
            '#'.$this->_leftDelimiter.'\s?(loop\s.+?)\s?'.$this->_rightDelimiter.'#i',
            '#'.$this->_leftDelimiter.'\s*(widget\s.+?)\s*'.$this->_rightDelimiter.'#is',
        );
        $viewContent = preg_replace_callback($patternArray, array($this, 'parseTags'), $viewContent);

        return $viewContent;
    }

    /**
     * 分析编辑视图标签
     *
     * @access protected
     *
     * @param string $tag 视图标签
     *
     * @return string
     */
    protected function parseTags($tag) {

        //变量分析
        $tag = stripslashes(trim($tag[1]));

        //当视图标签为空时
        if(!$tag) {
            return '';
        }

        //变量标签处理
        if (substr($tag, 0, 1) == '$') {
            return '<?php echo ' . $this->getVal($tag) . '; ?>';
        }

        //分析判断,循环标签
        $tagSel = explode(' ', $tag);
        $tagSel = array_shift($tagSel);
        switch ($tagSel) {

            case 'if' :
                return $this->compileIfTag(substr($tag, 3));
                break;

            case 'elseif' :
                return $this->compileIfTag(substr($tag, 7), true);
                break;

            case 'loop' :
                return $this->compileForeachStart(substr($tag, 5));
                break;

            default :
                return $tagSel;
        }
    }

    /**
     * 处理if标签
     *
     * @access public
     *
     * @param string $tagArgs 标签内容
     * @param bool $elseif 是否为elseif状态
     *
     * @return  string
     */
    protected function compileIfTag($tagArgs, $elseif = false) {

        //分析标签内容
        preg_match_all('#\-?\d+[\.\d]+|\'[^\'|\s]*\'|"[^"|\s]*"|[\$\w\.]+|!==|===|==|!=|<>|<<|>>|<=|>=|&&|\|\||\(|\)|,|\!|\^|=|&|<|>|~|\||\%|\+|\-|\/|\*|\@|\S#i', $tagArgs, $match);

        //当$match[0]不为空时
        $tokenArray = array_map(array($this, 'getVal'), $match[0]);
        $tokenString = implode(' ', $tokenArray);
        //清空不必要的内存占用
        unset($tokenArray);

        return ($elseif === false) ? '<?php if (' . $tokenString . ') { ?>' : '<?php } else if (' . $tokenString . ') { ?>';
    }

    /**
     * 处理foreach标签
     *
     * @access protected
     *
     * @param string $tagArgs 标签内容
     *
     * @return string
     */
    protected function compileForeachStart($tagArgs)
    {
        //分析标签内容
        preg_match_all('#(\$.+?)\s+(.+)#i', $tagArgs, $match);
        $loopVar = $this->getVal($match[1][0]);

        return '<?php if (is_array(' . $loopVar . ')) { foreach (' . $loopVar . ' as ' . $match[2][0] . ') { ?>';
    }

    /**
     * 处理视图标签中的变量标签
     *
     * @access protected
     *
     * @param string $val 标签名
     *
     * @return string
     */
    protected function getVal($val) {

        //当视图变量不为数组时
        if (strpos($val, '.') === false) {
            return $val;
        }

        $valArray = explode('.', $val);
        $_varName = array_shift($valArray);

        return $_varName . "['" . implode("']['", $valArray) . "']";
    }

    /**
     * 获取视图文件的路径
     *
     * @access protected
     *
     * @param string $fileName    视图名. 注：不带后缀
     *
     * @return string    视图文件路径
     */
    protected function getViewFile($fileName) {

        return $this->_viewPath . '/' . $fileName . $this->_viewExt;
    }

    /**
     * 获取视图编译文件的路径
     *
     * @access protected
     *
     * @param string $fileName 视图名. 注:不带后缀
     *
     * @return string
     */
    protected function getCompileFile($fileName) {

        return $this->_compilePath . '/' . $fileName . '.action.compilecache.php';
    }

    /**
     * 分析视图文件名
     *
     * @access publice
     *
     * @param string $fileName 视图文件名。注:名称中不带.php后缀。
     *
     * @return string
     */
    protected function parseViewName($fileName = null)
    {
        //参数分析
        if (!$fileName) {
            return $this->_controller . '/' . $this->_action;
        }

        $fileName = str_replace('.', '/', $fileName);
        if (strpos($fileName, '/') === false) {
            $fileName = $this->_controller . '/' . $fileName;
        }

        return $fileName;
    }

    /**
     * 缓存重写分析
     *
     * 判断缓存文件是否需要重新生成. 返回true时,为需要;返回false时,则为不需要
     *
     * @access protected
     *
     * @param string $viewFile 视图文件名
     * @param string $compileFile 视图编译文件名
     *
     * @return boolean
     */
    protected function isCompile($viewFile, $compileFile) {

        return (is_file($compileFile) && (filemtime($compileFile) >= filemtime($viewFile))) ? false : true;
    }

    /**
     * 创建视图的缓存文件
     *
     * @access protected
     *
     * @param string $content 缓存文件内容
     *
     * @return boolean
     */
    protected function createCache($content = null) {

        //判断当前的缓存文件路径
        if (!$this->_cacheFile) {
            return false;
        }

        //参数分析
        if (is_null($content)) {
            $content = '';
        }

        //分析缓存目录
        $cacheDir = dirname($this->_cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        return file_put_contents($this->_cacheFile, $content, LOCK_EX);
    }

    /**
     * 分析视图缓存文件名
     *
     * @access protected
     *
     * @param string $cacheId 视图文件的缓存ID
     *
     * @return string
     */
    protected function parseCacheFile($cacheId) {

        return $this->_cachePath .'/'. $this->_controller .  '/' . md5($cacheId) . '.action.html';
    }
}