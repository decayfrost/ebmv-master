<?php
/**
 * This is the help page
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class HelpController extends FrontEndPageAbstract
{
    /**
     * (non-PHPdoc)
     * @see FrontEndPageAbstract::_getEndJs()
     */
    protected function _getEndJs()
    {
        $js = parent::_getEndJs();
        $js .= 'pageJs.questions = ' . $this->_getQuestions() . ';';
        $js .= 'pageJs.init("topQs", "questionlist");';
        return $js;
    }
    private function _getQuestions()
    {
        $array = array(
			 array (
        		'en' => array (
        			'question' => "How can I access eBMV?"
        			,'answer'   => '<p>You can access eBMV Chinese Language e-Resource via the link on your library’s website. '
        				          .'You can browse and search catalogue, or preview e-books before you login. '
        				          .'If you’d like to read a whole e-book, or read newspaper and magazine, please login with your library card and PIN.</p>'
				)
			 	,'zh_cn' => array (
        			'question' => "如何使用eBMV中文电子资源?"
        			,'answer'  => '<p>你可以从图书馆网页上的链接进入eBMV中文电子资源网页. 在登录之前你可以浏览和检索目录, 也可以预览每本电子书的开始部分. '
			 			         .'如果你要阅读一本电子书的全部, 或者阅读报纸和杂志, 请先登录. 登录号是你的图书馆卡号和密码.</p>'
				)
			 	,'zh_tw' => array (
        			'question' => "如何使用eBMV中文電子資源? "
        			,'answer'  => '<p>你可以從圖書館網頁上的鏈接進入eBMV中文電子資源網頁. 在登錄之前你可以流覽和檢索目錄, 也可以預覽每本電子書的開始部份.'
			 			         .'如果你要閱讀一本電子書的全部, 或者閱讀報紙和雜誌, 請先登錄. 登錄號是你的圖書館卡號和 密碼.</p>'
				)
			 )
			 ,array (
        		'en' => array (
        			'question' => "How can I read Simplified Chinese e-books?"
        			,'answer'   => 'Click on the cover of the book you want to read. There are two buttons to select: <b>Read Online</b> and <b>Download This Book</b>. Read Online will work on anything that runs Windows, Mac OSX, iOS or Android.'
				)
			 	,'zh_cn' => array (
        			'question' => "如何在线阅读简体中文电子书?"
        			,'answer'  => '在目录上选择你要读的简体中文电子书, 点击封面.  在新的网页上有这本书的元数据, 还有两个按钮供选择: “在线阅读” 和 “下载阅读”.  在线阅读可在运行微软公司视窗, 苹果公司OS和安卓操作系统的桌上, 手提和平板电脑上实现. '
				)
			 	,'zh_tw' => array (
        			'question' => "如何在線閱讀簡體中文電子書?"
        			,'answer'  => '在目錄上選擇你要讀的簡體中文電子書, 點擊封面.  在新的網頁上有這本書的元數據, 還有兩個按鈕供選擇: “在線閱讀” 和“下載閱讀”.  在線閱讀可在運行微軟公司視窗, 蘋果公司OS和安卓操作系統的桌上, 手提和平板電腦上實現.'
				)
			 )
			 ,array (
        		'en' => array (
        			'question' => "What browsers are supported?"
        			,'answer'   => '<table class="table table-bordered"><tr><td>Microsoft</td><td>IE 8 or later</td></tr><tr><td>Apple</td><td>Safari</td></tr><tr><td>Google</td><td>Chrome</td></tr><tr><td>Mozilla</td><td>Firefox</td></tr></table>'
				)
			 	,'zh_cn' => array (
        			'question' => "哪些浏览器, 浏览器版本可以用来在线阅读?"
        			,'answer'  => '<table class="table table-bordered"><tr><td>微软公司</td><td>IE 8 or later</td></tr><tr><td>苹果公司</td><td>Safari</td></tr><tr><td>谷歌</td><td>Chrome</td></tr><tr><td>火狐</td><td>Firefox</td></tr></table>'
				)
			 	,'zh_tw' => array (
        			'question' => "哪些瀏覽器, 瀏覽器版本可以用來在線閱讀?"
        			,'answer'  => '<table class="table table-bordered"><tr><td>微軟公司</td><td>IE 8 or later</td></tr><tr><td>蘋果公司</td><td>Safari</td></tr><tr><td>谷歌</td><td>Chrome</td></tr><tr><td>火狐</td><td>Firefox</td></tr></table>'
				)
			 )
			 ,array (
        		'en' => array (
        			'question' => "How can I download and read Simplified Chinese e-books?"
        			,'answer'   => '<p>Select the Simplified Chinese e-book of interest in the catalogue, click its cover. The book’s metadata will appear on the new page. There are also two buttons to select: “Read Online” and “Download This Book”. </p><p>Downloaded e-book requires Microsoft Windows platform to open (Windows XP SP2, Vista, and Widows 7). Downloaded file is a compressed file bundled with reader. After unzip you will get an executable file (.exe). Double click the file to start reading<strong>*</strong>.</p><p><small><em>*Note: if Chinese is selected as the default language in MS Windows, the downloaded file’s name will appear as a string of strange characters. It will not affect you open the file.</em></small></p>'
				)
			 	,'zh_cn' => array (
        			'question' => "如何下载阅读简体中文电子书?"
        			,'answer'  => '<p>在目录上选择你要读的简体中文电子书, 点击封面. 在新的网页上有这本书的元数据, 还有两个按钮供选择: “在线阅读” 和 “下载阅读”.</p><p>下载阅读现只可以在运行微软公司视窗操作系统(Windows XP SP2, Vista, Windwons7, Windows8)的桌上, 手提和平板电脑上实现. 下载的文件是一个已包含阅读器的压缩文件(.zip). 经解压之后得到一个执行文件(.exe). 双击此文件即可开始阅读<strong>*</strong>.</p><p><small><em>*注: 如果没有在微软视窗中选择中文作为默认语言, 下载文件的文件名会是一串奇怪的字符.但这并不影响你打开此文件.</em></small></p>'
				)
			 	,'zh_tw' => array (
        			'question' => "如何下載閱讀簡體中文電子書?"
        			,'answer'  => '<p>在目錄上選擇你要讀的簡體中文電子書, 點擊封面.在新的網頁上有這本書的元數據, 還有兩個按鈕供選擇: “在線閱讀” 和“下載閱讀”.</p><p>下載閱讀現只可以在運行微軟公司視窗操作系統(Windows XP SP2, Vista, Windows7)的桌上, 手提和平板電腦上實現. 下載的文件是一個已包含閱讀器的壓縮文件(.zip).  經解壓之後得到一個執行文件(.exe).  雙擊此文件即可開始閱讀<strong>*</strong>.</p><p><small><em>*注: 如果沒有在微軟視窗中選擇中文作為默認語言, 下載文件的文件名會是一串奇怪的字符.但這並不影響你打開此文件.</em></small></p>'
				)
			 )
			 ,array (
        		'en' => array (
        			'question' => "How can I read Simplified Chinese e-newspapers and e-magazines?"
        			,'answer'   => 'Simplified Chinese e-newspaper, e-magazine and Traditional Chinese e-newspaper service supports online reading. Readers can enter eBMV Chinese e-resource page via the link on library website. You can browse and search before log in. After log in, select interested e-newspaper or e-magazine and read on desktops, laptops and tablets with Microsoft Windows, Apple OS and Android operating system.'
				)
			 	,'zh_cn' => array (
        			'question' => "如何阅读简体中文电子报刊, 电子杂志?"
        			,'answer'  => '简体中文电子报刊, 电子杂志和繁体中文报纸服务支持在线阅读. 读者可以从图书馆网页上的链接进入eBMV中文电子资源网页. 在登录之前你可以浏览和检索目录. 登录后选择中意的电子报刊, 在运行微软公司视窗, 苹果公司OS或安卓操作系统的桌上, 手提和平板电脑上阅读.'
				)
			 	,'zh_tw' => array (
        			'question' => "如何閱讀簡體中文電子報刊, 電子雜誌?"
        			,'answer'  => '簡體中文電子報刊, 電子雜誌和繁體中文雜誌服務支持在綫閱讀. 讀者可以從圖書館網頁上的鏈接進入eBMV中文電子資源網頁. 在登錄之前你可以流覽和檢索目錄. 登錄后選擇中意的電子報刊, 在運行微軟公司視窗, 蘋果公司OS 或安卓操作系統的桌上, 手提和平板電腦上閱讀.'
				)
			 )
			 ,array (
        		'en' => array (
        			'question' => "How can I read Traditional Chinese e-books and e-magazines?"
        			,'answer'   => '<p>Select the traditional Chinese e-book or e-magazine you want to read, click its cover. This publication’s metadata will appear on a new web page, together with a “Borrow” button. After borrow the e-book or e-magazine you can read it online using a desktop, notebook or tablet.</p>'
        						  .'<p>If you’d like to use an Apple or Android mobile device, please go to Apple App Store or Google Play and download<strong>臺灣雲端書庫2014</strong> app.</p>'
        				          .'<p>You can borrow up to 10 Traditional Chinese e-books and e-magazines, loan period is two weeks. eBMV will automatically return Traditional Chinese e-books and e-magazines when it’s due.</p>'
				)
			 	,'zh_cn' => array (
        			'question' => "如果阅读繁体中文电子书和电子杂志?"
        			,'answer'  => '<p>在目录上选择你要阅读的繁体中文电子书和电子杂志, 点击封面. 在新的网页上有此出版物的元数据, 以及一个”借阅”按钮. 借阅后你即可以在PC, NB, 平板电脑上连线阅读.</p>'
			 					 .'<p>如要用苹果或安卓移动终端, 需先到Apple App Store或 Google Play 下載<strong>臺灣雲端書庫2014</strong> app.</p>'
			 					 .'<p>你可以最多借十本中文繁体电子书和电子杂志., 借阅期限是两个星期. eBMV 会自动归还到期的中文繁体电子书和电子杂志.</p>'
				)
			 	,'zh_tw' => array (
        			'question' => "如果閱讀繁體中文電子書和電子雜誌?"
        			,'answer'  => '<p>在目錄上選擇你要閱讀的繁體中文電子書 和電子雜誌, 點擊封面. 在新的網頁上有此出版物的元數據, 以及一個”借閱”按鈕. 借閱后你即可以在PC, NB, 平板電腦 上連線閱讀.</p>'
			 					 .'<p>如要用苹果或安卓移动终端, 需先到Apple App Store或 Google Play 下載<strong>臺灣雲端書庫2014</strong> app.</p>'
			 					 .'<p>你可以最多借十本中文繁體電子書和電子雜誌., 借閱期限是兩個星期. eBMV 會自動歸還到期的中文繁體電子書和電子雜誌.</p>'
				)
			 )
			 ,array (
        		'en' => array (
        			'question' => "How to access ELS and Chinese language courses?"
        			,'answer'  => '<p>From eBMV you can access wide range English language courses from beginners’ to IELTS provided by Xin Dong Fang; '
			 				     .'and various levels of Chinese language courses from Confucius Institute. Currently there are three beginners’ level '
			 			         .'Chinese language courses available as free samples. Please select one of them under “学中文“ by single mouse click. '
			 			         .'The course will start. The list under ESL will display the Xin Dong Fang English courses your library subscribed to.</p>'
				)
			 	,'zh_cn' => array (
        			'question' => "怎样学英语和学中文课程?"
        			,'answer'  => '<p>从eBMV你可以使用由新东方提供的从初级到雅思各种级别英语课程; 以及不同程度的孔子学院中文教程. 现在有三个初学者中文课程样本可供使用.'
			 			         .' 请单击鼠标在”学中文”下面选择一个课程, 就开始上课了. 在“ESL”下面的课程表会列出你的图书馆已采购的新东方英语课程.</p>'
				)
			 	,'zh_tw' => array (
        			'question' => "怎樣學英語和學中文課程?"
        			,'answer'  => '<p>從eBMV你可以使用由新東方提供的從初級到雅思各種級別英語課程; 以及不同程度的孔子學院中文教程. 現在有三個初學者中文課程樣本可供使用.'
			 			         .' 請單擊鼠標在”學中文”下麵選擇一個課程, 就開始上課了. 在“ESL”下麵的課程表會列出你的圖書館採購的新東方的英語課程.</p>'
				)
			 )
        );
        return json_encode($array);
    }
}