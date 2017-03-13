<?php
include_once 'header.php';
include_once 'standart-aside.php'; ?>
<section class="main-content">
    <div class="secondary-banner">
    </div>  
    <div class="main-content-text">
        <h1>О компании</h1> 
        <h2>Рейтинг популярности</h2>
        
<script>
var show;
function hidetxt(type){
 param=document.getElementById(type);
 if(param.style.display == "none") {
 if(show) show.style.display = "none";
 param.style.display = "block";
 show = param;
 }else param.style.display = "none"
}
</script>

<div>
<a onclick="hidetxt('div1'); return false;" href="#" rel="nofollow">Ссылка 1</a>
<div style="display:none;" id="div1">
Много много много текста 1
</div>
</div>
<div>
<a onclick="hidetxt('div2'); return false;" href="#" rel="nofollow">Ссылка 2</a>
<div style="display:none;" id="div2">
Много много много текста 2
</div>
</div>
<div>
<a onclick="hidetxt('div3'); return false;" href="#" rel="nofollow">Ссылка 3</a>
<div style="display:none;" id="div3">
Много много много текста 3
</div>
</div>
         </div>     
</section>    
<?php include_once 'footer.php'; ?>
