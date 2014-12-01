<?php
class FancyBox extends TClientScript
{
	/**
     * (non-PHPdoc)
     * @see TPage::render()
     */
    public function onPreRender($param)
    {
        parent::onPreRender($param);
        $clientManger = $this->getPage()->getClientScript();
        $clientManger->registerPradoScript('ajax');
        $cScripts = FrontEndPageAbstract::getLastestJS(get_class($this));
        if (isset($cScripts['js']) && ($lastestJs = trim($cScripts['js'])) !== '')
            $clientManger->registerScriptFile('FancyBoxJs', $this->publishAsset($lastestJs));
        if (isset($cScripts['css']) && ($lastestCss = trim($cScripts['css'])) !== '')
            $clientManger->registerStyleSheetFile('FancyBoxCss', $this->publishAsset($lastestCss));
        
        $clientManger->registerScriptFile('FancyBoxMouseWheelJs', Prado::getApplication()->getAssetManager()->publishFilePath(dirname(__FILE__) . '/lib/jquery.mousewheel-3.0.6.pack.js'));
        $clientManger->registerScriptFile('FancyBoxPackJs', Prado::getApplication()->getAssetManager()->publishFilePath(dirname(__FILE__) . '/lib/jquery.fancybox.pack.js'));
        $clientManger->registerStyleSheetFile('FancyBoxPackCss', Prado::getApplication()->getAssetManager()->publishFilePath(dirname(__FILE__) . '/lib/jquery.fancybox.css'), 'screen');
        
        foreach (glob(dirname(__FILE__) . "/lib/*.{jpg,gif,png,bmp}", GLOB_BRACE) as $img)
        	Prado::getApplication()->getAssetManager()->publishFilePath($img);
    }
}