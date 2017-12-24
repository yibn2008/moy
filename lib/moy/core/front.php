<?php
/**
 * PHP Version > 5.3
 *
 * Copyright (c) 2012, Zoujie Wu <yibn2008@gmail.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @version    SVN $Id: front.php 194 2013-04-11 07:46:05Z yibn2008@gmail.com $
 * @package    Moy/Core
 */

/**
 * 前端控制器类
 *
 * @dependence Moy(Moy_Config, Moy_Request), Moy_Bootstrap, Moy_Exception_Http404
 * @author     Zoujie Wu <yibn2008@gmail.com>
 * @copyright  2012, Zoujie Wu <yibn2008@gmail.com>
 * @license    BSD License <http://www.opensource.org/licenses/bsd-license.php>
 * @package    Moy/Core
 * @version    1.0.0
 * @since      Release 1.0.0
 */
class Moy_Front
{
    /**
     * 前端控制器动态配置
     *
     * 说明: 此方法用于配置PHP执行环境,在此方法被调用时,除了config/loader被预先加载之外,
     * 其它基础组件均未初始化,可以通过重写此方法来自定义PHP执行配置
     */
    public function configure()
    {
        $config = Moy::getConfig();

        //设置时区
        date_default_timezone_set($config->get('site.timezone'));

        //设置日志与调试变量
        error_reporting($config->get('debug.error_level'));
    }

    /**
     * 在front运行之前调用,此时框架已经初始化完毕,正准备执行框架业务逻辑,可以通过重写此方法
     * 做一些全局性的工作
     */
    public function beforeRun()
    {
        //empty
    }

    /**
     * 运行框架
     */
    public function run()
    {
        $state       = 200;
        $redirect    = null;
        $handler     = null;
        $render_view = false;
        $controller  = null;
        $action      = null;
        $request     = Moy::getRequest();
        $response    = Moy::getResponse();
        $session     = Moy::getSession();
        $router      = Moy::getRouter();
        $auth        = Moy::getAuth();
        $user        = $auth->authenticate();

        //初始化路由,并获取控制器与动作
        try {
            $request->initRouting($router);
            $controller = $request->getController();
            $action = $request->getAction();
        } catch (Moy_Exception_Router $mex_router) {
            $state = 404;
        }

        if (Moy::isLog()) {
            Moy::getLogger()->info('Front', "Init routing: state = {$state}, controller = {$controller}, action = {$action}");
        }

        //访问权限检测
        if ($state == 200) {
            if (!$user->isAllow($controller, $action)) {
                $state = 403;
            }

            if (Moy::isLog()) {
                Moy::getLogger()->info('Front', 'Authenticate user: state = ' . $state . ', roles = ' . implode(',', $user->getRoles()));
            }
        }

        //加载控制器并执行相关动作
        if ($state == 200) {
            try {
                $handler = $this->_call($controller, $action);
                $render_view = true;
            } catch (Moy_Exception_Http404 $mex_404) {
                $state = 404;
            } catch (Moy_Exception_Http403 $mex_403) {
                $state = 403;
            } catch (Moy_Exception_Redirect $mex_red) {
                $redirect = $mex_red;
                $state = 301;
            }

            if (Moy::isLog()) {
                Moy::getLogger()->info('Front', "Load controller and execute action: state = {$state}");
            }
        }

        if ($state != 200) {
            //处理404异常
            if ($state == 404) {
                $page_404 = Moy::getConfig()->get('site.page_404');
                if ($page_404) {
                    $response->setLocation($router->url($page_404));
                    $session->setFlashMsg(Moy_Session::FLASH_MSG_HTTP404, $request->getUrl());
                } else {
                    $render_view = true;
                }
            }
            //处理403异常
            else if ($state == 403) {
                $page_403 = Moy::getConfig()->get('site.page_403');
                if ($page_403) {
                    $response->setLocation($router->url($page_403));
                    $session->setFlashMsg(Moy_Session::FLASH_MSG_HTTP403, $request->getUrl());
                } else {
                    $render_view = true;
                }
            }
            //处理重定向
            else if ($state == 301) {
                if ($redirect->getInfo()) {
                    $page_flash = Moy::getConfig()->get('site.page_flash');
                    if ($page_flash) {
                        $response->setLocation($router->url($page_flash));
                        $session->setFlashMsg(Moy_Session::FLASH_MSG_REDIRECT, array(
                                'url' => $redirect->getUrl(),
                                'info' => $redirect->getInfo()
                        ));
                    } else {
                        $render_view = true;
                    }
                } else {
                    $response->setLocation($redirect->getUrl());
                }
            }
        }

        //输出HTTP头部
        $response->sendHeaders();
        if (Moy::isLog()) {
            Moy::getLogger()->info('Front', 'Send http headers, render view ? ' . ($render_view ? 'YES' : 'NO'), $response->exportHeaders(true));
        }

        //输出HTTP正文
        if ($response->hasBody()) {
            $response->sendBody();
        } else if ($render_view) {
            //输出视图
            $view = Moy::getView();
            switch ($state) {
                case 200:
                    if ($handler instanceof Moy_Controller) {
                        $template = $handler->getTemplate();
                        $layout   = $handler->getLayout();
                        $metas    = $handler->getMetas();
                        $vars     = $handler->exportVars();
                        $styles   = $handler->getStyles();
                        $scripts  = $handler->getScripts();
                        $view->render($template, $layout, $metas, $vars, $styles, $scripts);
                    }
                    break;
                case 301:
                    $view->renderFlash($redirect->getUrl(), $redirect->getInfo());
                    break;
                case 403:
                    $view->render403();
                    break;
                case 404:
                    $view->render404();
                    break;
                default:
                    //do nothing
            }
        }
    }

    /**
     * 在front运行之后调用,此时框架已经完成业务逻辑和视图渲染,可重写此方法来做一些收尾的工作
     */
    public function afterRun()
    {
        //empty
    }

    /**
     * 调用控制器与动作
     *
     * @param  string $controller 控制器名
     * @param  string $action     动作名
     * @throws Moy_Exception_Http404      执行动作时可以抛出404异常
     * @throws Moy_Exception_BadInterface 控制器未继承于Moy_Controller
     * @return Moy_Controller             调用的控制器实例
     */
    protected function _call($controller, $action)
    {
        $class = 'Controller_' . str_replace(' ', '_', ucwords(str_replace('/', ' ', $controller)));

        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $ex) {
            throw new Moy_Exception_Http404();
        }
        if (!$reflection->isSubclassOf('Moy_Controller')) {
            throw new Moy_Exception_BadInterface('Moy_Controller');
        }
        $object = $reflection->newInstance();

        return $object->execute($action);
    }
}