<?php
/**
 * action class Action_Default_Http404 for testing
 */
class Action_Default_Http404 extends Moy_Controller_Action
{
    public function execute(Moy_Request $request)
    {
        Moy::set('msg-from-act-404', 'It\'s me', true);
    }
}
