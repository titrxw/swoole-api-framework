<?php
namespace framework\base;

class Documentor extends Component
{
    protected $_docblock;
    protected $_docs;
    protected $_handle;
    protected $_class;
    protected $_method;

    protected function getDocHandle()
    {
      if (!$this->_handle) {
        $this->_handle = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
      }
      return $this->_handle;
    }

    public function parse($class, $method)
    {
        $this->_class = \get_class($class);
        $this->_method = $method;
        if (isset($this->_docblock[$this->_class][$this->_method])) {
            return $this;
        }
        $methodReflection = new \ReflectionMethod($class, $method);
        $this->getDocHandle();
        $this->_docblock[$this->_class][$this->_method] = $this->_handle->create($methodReflection->getDocComment());
        return $this;
    }

    public function getTags($name)
    {
        if (isset($this->_docs[$this->_class][$this->_method][$name])) {
            return $this->_docs[$this->_class][$this->_method][$name];
        }
        $tags = [];
        foreach ($this->_docblock[$this->_class][$this->_method]->getTagsByName($name) as $item) {
          if ($item instanceof \phpDocumentor\Reflection\DocBlock\Tags\Generic) {
            $tags[] = $item->getDescription()->render();
          } else if ($item instanceof \phpDocumentor\Reflection\DocBlock\Tags\Method) {
            $tags[] = $item->getMethodName();
          }
        }
        $this->_docs[$this->_class][$this->_method][$name] = $tags;
        return $tags;
    }
}