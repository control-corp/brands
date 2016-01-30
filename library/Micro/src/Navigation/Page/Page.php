<?php

namespace Micro\Navigation\Page;

use Micro\Application\Route;

class Page extends AbstractPage
{
    protected $alias;
    protected $href;
    protected $uri;
    protected $routeParams = [];
    protected $route;
    protected $reset = \true;
    protected $qsa = \false;
    protected $active = \null;

    public function isActive($recursive = \false)
    {
        if ($this->uri !== \null) {
            $this->detectUri();
        } else {
            $this->detectRoute();
        }

        if ($this->active === \false && $recursive) {
            foreach ($this->pages as $child) {
                if ($child instanceof Page && $child->isActive(\true)) {
                    return \true;
                }
            }
            return \false;
        }

        return $this->active;
    }

    public function detectUri()
    {
        if (\null === $this->active) {
            if (preg_match('~^' . preg_quote(app('request')->getRequestUri()). '$~ius', $this->uri)) {
                $this->active = \true;
            } else {
                $this->active = \false;
            }
        }
    }

    public function detectRoute()
    {
        if (\null === $this->active) {

            $route = app('router')->getCurrentRoute();

            if ($route === \null || $route->getName() !== $this->route) {
                $this->active = \false;
                return;
            }

            $reqParams = app('request')->getParams();

            $pageRoute = app('router')->getRoute($this->route);

            $myParams = $this->routeParams + ($pageRoute ? $pageRoute->getParams() : []);

            foreach ($myParams as $key => $value) {
                if (\null === $value) {
                    unset($myParams[$key]);
                }
            }

            if (count(array_intersect_assoc($reqParams, $myParams)) == count($myParams)) {
                $this->active = \true;
            } else {
                $this->active = \false;
            }
        }
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getHref()
    {
        if (\null === $this->href) {
            if ($this->uri !== \null) {
                $this->href = (string) $this->uri;
            } else {
                $this->href = (string) route($this->route, $this->routeParams, $this->reset, $this->qsa);
            }
        }

        return $this->href;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    public function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setReset($reset)
    {
        $this->reset = (bool) $reset;

        return $this;
    }

    public function setQsa($qsa)
    {
        $this->qsa = (bool) $qsa;

        return $this;
    }

    public function isAllowed($role = \null)
    {
        if ($this->uri !== \null) {
            return \true;
        }

        $route = app('router')->getRoute($this->route);

        if (!$route instanceof Route) {
            return \false;
        }

        $route->setParams($this->routeParams);

        $resource = $route->getHandler();

        if (!is_string($resource) || is_allowed($resource, $role)) {
            return \true;
        }

        return \false;
    }
}