<?php
namespace framework\base;

class Documentor extends Component
{
    const ALL = '__all__';
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

        $doc = $methodReflection->getDocComment();
        $this->_docblock[$this->_class][$this->_method] = $this->_handle->create($doc ? $doc : 'null');
        return $this;
    }

    public function getTags($name = null)
    {
        if (!$name) {
          $name = static::ALL;
        }
        if (isset($this->_docs[$this->_class][$this->_method][$name])) {
            return $this->_docs[$this->_class][$this->_method][$name];
        }
        
        $tags = [];
        if ($name == static::ALL) {
          $_tags = $this->_docblock[$this->_class][$this->_method]->getTags();
        } else {
          $_tags = $this->_docblock[$this->_class][$this->_method]->getTagsByName($name);
        }
        
        foreach ($_tags as $item) {
          if ($item instanceof \phpDocumentor\Reflection\DocBlock\Tags\Generic) {
            if ($name == static::ALL) {
              $tags[$item->getName()][] = $item->getDescription()->render();
            } else {
              $tags[] = $item->getDescription()->render();
            }
          } else if ($item instanceof \phpDocumentor\Reflection\DocBlock\Tags\Method) {
            if ($name == static::ALL) {
              $tags[$item->getName()][] = $item->getMethodName();
            } else {
              $tags[] = $item->getMethodName();
            }
          }
        }
        $this->_docs[$this->_class][$this->_method][$name] = $tags;
        return $tags;
    }
}
