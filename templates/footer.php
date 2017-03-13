<footer>
    <div class="footer-menu">
        <ul class="footer-menu-list">
            <li><a href="/about"><?=Lang::get()->translate('About company');?></a></li>
            <li><a href="/"><?=Lang::get()->translate('Ads');?></a></li>
            <li><a href="/privacy"><?=Lang::get()->translate('Privacy');?></a></li>
            <li><a href="/feedback"><?=Lang::get()->translate('Feedback');?></a></li>
            <li><a href="/help"><?=Lang::get()->translate('Help');?></a></li>
            <li><a href="/"><?=Lang::get()->translate('Language');?></a></li>
        </ul>
    </div>
    <div class="footer-copyright">
        <a href="http://m-artkzn.ru"><img src="http://<?=$GLOBALS['config']['domain']?>/assets/images/m-art.jpg" alt="Magenta Art"></a>
        <div class="footer-copyright-text">
            <p><?=Lang::get()->translate('Website was created in Magenta Art Studio');?>.</p>
            <p><a href="http://m-artkzn.ru/услуги/создание-сайтов/"><?=Lang::get()->translate('Website development');?></a></p>
        </div>    
    </div>    
</footer>
     </div>
    <script src="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/js/jquery-1.12.1.min.js"></script>
    <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/jquery-ui.min.js"></script>
    <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/jquery.selectBoxIt.min.js"></script>
    <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/select2.min.js"></script>
    
    <?php 
        // Проверяем, что это страница пользователя. Подключаем js Emoji
        preg_match("/id([0-9]*)/si", $_SERVER['REQUEST_URI'], $matches);
        if ( is_numeric( $matches[1] ) ||  ($_SERVER['REQUEST_URI'] == '/me' ) ) { ?>
            <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/emoji/nanoscroller.min.js"></script>
            <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/emoji/tether.min.js"></script>
            <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/emoji/config.js"></script>
            <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/emoji/util.js"></script>
            <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/emoji/jquery.emojiarea.js"></script>
            <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/emoji/emoji-picker.js"></script>
            <script type="text/javascript">
                // Initializes and creates emoji set from sprite sheet
                window.emojiPicker = new EmojiPicker({
                    emojiable_selector: '[data-emojiable=true]',
                    assetsPath: '/assets/images/emoji',
                    popupButtonClasses: 'icon icon-smile'
                });
                // Finds all elements with `emojiable_selector` and converts them to rich emoji input fields
                // You may want to delay this step if you have dynamically created input fields that appear later in the loading process
                // It can be called as many times as necessary; previously converted input fields will not be converted again
                window.emojiPicker.discover();

                $('.comment-block').each(function () {
                    var text = EmojiPicker.prototype.unicodeToImage($(this).find('.comment-text').html());
                    $(this).find('.comment-text').html(text);
                });
            </script>
        <?php } ?>
            
            
    <?php 
        if ( $_SERVER['REQUEST_URI'] == '/feedback' ) { 
            // Отправка писем только с этой страницы ?>
            <script type="text/javascript">
                $('form.ajax-form').submit(function() {
                    var o = $(this);
                    o.find('[type="submit"]').html('Отправка...').addClass('disabled');
                    $.ajax({
                        type: o.attr('method'),
                        url: o.attr('action'),
                        data: o.serialize(),
                        success: function (data) {
                            var sent = false;
                            if (data == 'ok') {
                                o.find('input:not([type="hidden"]), select, textarea').val('');
                                o.find('[type="submit"]').html('Отправлено');
                                sent = true;
                            } else {
                                o.find('[type="submit"]').html('Ошибка :(').addClass('has-error');
                                o.find('[name="' + data + '"]').focus().parent().addClass('has-error');
                            }
                            setTimeout(function() {
                                o.find('[type="submit"]').html('Отправить').removeClass('disabled has-error');
                                if (sent) {
                                    o.find('input:not([type="hidden"]), select, textarea').val('');
                                }
                            }, 5000);
                        },
                        error: function () {
                            o.find('[type="submit"]').html('Ошибка :(').addClass('has-error');
                            setTimeout(function() {
                                o.find('[type="submit"]').html('Отправить').removeClass('disabled has-error');
                            }, 5000);
                        }
                    });
                    return false;
                });
            </script>
    <?php } ?>
            
    <?php if ( $_SERVER['REQUEST_URI'] == '/help' ) { ?> 
        <script type="text/javascript">
            $('section.main-content.help .answer').on('click', function() {
                if (!$(this).find('p').is(':visible')) {
                    $('section.main-content.help .answer p').slideUp();
                    $(this).find('p').slideDown();
                } else {
                    $(this).find('p').slideUp();
                }
            });
        </script>
    <?php } ?> 
    
    <script src="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/js/common.js"></script>
    <script src="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/js/slick.min.js"></script>
    <script src="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/js/responsive.js"></script>
    
     <!--LiveInternet counter--><script type="text/javascript"><!--
    document.write("<a href='//www.liveinternet.ru/click' "+
    "target=_blank><img style='display:none!IMPORTANT;' src='//counter.yadro.ru/hit?t14.6;r"+
    escape(document.referrer)+((typeof(screen)=="undefined")?"":
    ";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
    screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
    ";"+Math.random()+
    "' alt='' title='LiveInternet: показано число просмотров за 24"+
    " часа, посетителей за 24 часа и за сегодня' "+
    "border='0' width='0' height='0'><\/a>")
    //--></script><!--/LiveInternet-->
     <!-- Yandex.Metrika counter --> <script type="text/javascript"> (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter38721305 = new Ya.Metrika({ id:38721305, clickmap:true, trackLinks:true, accurateTrackBounce:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/38721305" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->
   </body>
</html>