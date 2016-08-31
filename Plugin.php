<?php
/**
 * 简洁美观非常Qの悬浮音乐播放器
 * 
 * @package QPlayer
 * @author Jrohy
 * @version 1.2.1
 * @link https://32mb.space
 */
class QPlayer_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->header = array('QPlayer_Plugin', 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('QPlayer_Plugin', 'footer');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
   
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){

        $autoPlay = new Typecho_Widget_Helper_Form_Element_Radio(
        'autoPlay', array('0'=> '关闭', '1'=> '开启'), 0, '自动播放',
            '');
        $form->addInput($autoPlay);

        $rotate = new Typecho_Widget_Helper_Form_Element_Radio(
        'rotate', array('0'=> '关闭', '1'=> '开启'), 0, '封面旋转',
            '');
        $form->addInput($rotate);

        $color = new Typecho_Widget_Helper_Form_Element_Text('color', NULL, '', _t('自定义主色调'), _t('默认为<span style="color: #1abc9c;">#1abc9c</span>, 你可以自定义任何你喜欢的颜色作为播放器主色调。自定义主色调必须使用 Hex Color, 即`#233333`或`#333`的格式。填写错误的格式可能不会生效。'));
        $form->addInput($color);

        $getMusic = new Typecho_Widget_Helper_Form_Element_Radio('getMusic',NULL,NULL,_t('添加网易云音乐(主机需支持curl扩展)'),_t('
        	<div style="background-color:#467b96;padding:5px 8px;max-width:110px;border-radius: 2px;"><a href="'.Helper::options()->pluginUrl.'/QPlayer/IDExplain.php" target="_blank" style="font-size:14px;color:#fff;outline:none;text-decoration:none;">网易云音乐id解析</a>
        	</div>请自行去网易云音乐网页版获取音乐id(具体在每个音乐项目的网址最后会有个id)。<b>将解析出的音乐链接复制到下面歌曲列表里(注意检查与现有歌曲是否用英文,隔开)</b>'));
        $form->addInput($getMusic);

        $musicList = new Typecho_Widget_Helper_Form_Element_Textarea('musicList', NULL, 
'{
    title: "叫做你的那个人",
    artist: "Jessica",
    cover: "https://obw915dkh.qnssl.com/cover/%E5%8F%AB%E5%81%9A%E4%BD%A0%E7%9A%84%E9%82%A3%E4%B8%AA%E4%BA%BA.jpg",
    mp3: "https://obw92zax9.qnssl.com/%E5%8F%AB%E5%81%9A%E4%BD%A0%E7%9A%84%E9%82%A3%E4%B8%AA%E4%BA%BA.mp3",
},
{
	title: "如果",
	artist: "金泰妍",
	cover: "https://obw915dkh.qnssl.com/cover/%E5%A6%82%E6%9E%9C.jpg",
	mp3: "https://obw92zax9.qnssl.com/%E5%A6%82%E6%9E%9C.mp3",
}',_t('歌曲列表'), _t('格式: {title:"xxx", artist:"xxx", cover:"http:xxxx", mp3:"http:xxxx"} ，每个歌曲之间用英文,隔开。请保证歌曲列表里至少有一首歌！'));
        $form->addInput($musicList);
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 输出头部css
     * 
     * @access public
     * @return void
     */
    public static function header(){
        $cssUrl = Helper::options()->pluginUrl . '/QPlayer/css/player.css';
        echo '<link rel="stylesheet" href="' . $cssUrl . '">';
    }
    /**
     * 输出底部
     * 
     * @access public
     * @return void
     */
    public static function footer(){
        $options = Typecho_Widget::widget('Widget_Options')->plugin('QPlayer'); 
		echo '
			<div id="QPlayer" style="z-index:2016">
			<div id="pContent">
				<div id="player">
					<span class="cover"></span>
					<div class="ctrl">
						<div class="musicTag marquee">
							<strong>Title</strong>
							 <span> - </span>
							<span class="artist">Artist</span>
						</div>
						<div class="progress">
							<div class="timer left">0:00</div>
							<div class="contr">
								<div class="rewind icon"></div>
								<div class="playback icon"></div>
								<div class="fastforward icon"></div>
							</div>
							<div class="right">
								<div class="liebiao icon"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="ssBtn">
				        <div class="adf"></div>
			    </div>
			</div>
			<ol id="playlist"></ol>
			</div>
             ';

        if($options->color != '') {
            echo '<style>
            #pContent .ssBtn {
                background-color:'.$options->color.';
            }
            #playlist li.playing, #playlist li:hover{
                border-left-color:'.$options->color.';
            }
            </style>';
        }
        echo '<script src="'. Helper::options()->pluginUrl . '/QPlayer/js/jquery.min.js"></script>';
        echo '
            <script>
              var autoplay = '.$options->autoPlay.';
              var playlist = [
              '.$options->musicList.'
              ];
              var isRotate = '.$options->rotate.';
            </script> ' . "\n";
		echo '<script  src="'.Helper::options()->pluginUrl . '/QPlayer/js/jquery-ui.min.js"></script>' . "\n";
        echo '<script  src="'.Helper::options()->pluginUrl . '/QPlayer/js/jquery.marquee.min.js"></script>' . "\n";
        echo '<script  src="'.Helper::options()->pluginUrl . '/QPlayer/js/player.js"></script>' . "\n";
        
    }

}