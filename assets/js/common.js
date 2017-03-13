$(document).ready(function () {
    var timeout; // Для ввода в поле поиска

    if ($.browser.msie && ($.browser.version == 9 || $.browser.version == 10 || $.browser.version == 11)) {
        var inputs = $('input[type="text"], input[type="phone"], textarea, input[type="password"]');
        $.each(inputs, function (index, value) {
            var plchld = $(this).attr('placeholder');
            $(this).addClass('ie-9-plchld');
            $(this).val(plchld);
        });

        $('.ie-9-plchld').on('focus', function () {
            $(this).val('');
        });

        $('.ie-9-plchld').on('blur', function () {
            $(this).val($(this).attr('placeholder'));
        });
    }
    //$('.custom-select').selectBoxIt({autoWidth:false}); 
    if ($(window).width() >= '1200') {
        $('.custom-select').select2();
    }
    $('.main-banner-aside').slick();
    $('.socnet-slider-block').slick({
        slidesToShow: 7,
        sliderToScroll: 1
    });

    $('.js-link').live('click', function () {
        var link = $(this).data('link');
        if (link != '') {
            location.href = link;
        }
    });

    $('.pass-action').on('click', function () {
        passActions.call(this);
    });
    $('#mainSearchForm.active-search select').live('change', function () {
        var data = $(this).parents('#mainSearchForm').serialize();
        mainSearchAjaxStart();
        getMainSearchResult(data);
        mainSearchAjaxStop();
    });

    $('#mainSearchForm select[name="country"]').on('change', function () {
        var countryId = $(this).val();
        //setCityToDefault();
        getCities(countryId);
    });

    $('#mainSearchForm.active-search input').on('keyup', function () {
        var data = $(this).parents('#mainSearchForm').serialize();
        delay(function () {
            mainSearchAjaxStart();
            getMainSearchResult(data);
            mainSearchAjaxStop();
        }, 1000);
    });

    $('.main-menu-aside .main-menu-header-block .block-header-title a').on('click', function () {
        $('.main-menu-aside .main-menu-enter-form-block').slideToggle();
        $('.socnet-slider-block').slick('setPosition');
    });

    $('.stats-like .stats-in-block i').click(function () {
        if (typeof ($('.sidebar-user-info a').attr('href')) != 'undefined') {
            var userId = $(this).parents('.stats-like').data('user-id');
            var subject = $(this).attr('id');
            var action = 'addLike';
            if (!$(this).parent('.stats-in-block').siblings('.stats-in-block').children('i').hasClass('active')) {
                if ($(this).hasClass('active')) {
                    action = 'removeLike';
                }
                likesActions.call(this, userId, subject, action);
            }
        } else {
            // Пользователь не авторизован
        }
    });

    $('#userCommentForm textarea').on('keydown', function (e) {
        if (e.which == 13 && e.ctrlKey) {
            $('#userCommentForm').submit();
        }
    });

    $('#userCommentForm').on('submit', function () {
        return writeComment.call(this);
    });
});

function writeComment() {
    if ($(this).children('#addComment').val() != '') {
        var data = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: $(this).attr('action') + "?json",
            data: data,
            success: function (data) {
//                console.log(data);
                var comment = data.data.comment;
                var cb = $(".comment-block.hidden").clone();
                cb.find(".author-name span, .avatar").append($("<a href='" + cb.data('href') + comment.author + "'></a>"));
                cb.find(".avatar a").append($('<img src="' + cb.find(".avatar").data('src') + (comment.avatar ? comment.avatar : 'unknown.png') + '">'));
                cb.find(".author-name span a").text(comment.name);
                cb.find(".datetime span").text(comment.datetime);
                cb.find(".comment-text p").html(EmojiPicker.prototype.unicodeToImage(comment.text));
                cb.removeClass("hidden");
                $(".comment-block.hidden").before(cb);
                $(".emoji-wysiwyg-editor").empty();
            },
            error: function (data) {

            }
        });
    }
    return false;
}

function likesActions(userId, subject, action) {
//    console.log(action);
    var elContext = $(this);
    if (userId != 'undefined') {
        var like;
        if (subject == 'user-like') {
            like = 1;
        }
        if (subject == 'user-dislike') {
            like = 0;
        }
        $.ajax({
            type: 'POST',
            url: '/likes/' + action,
            data: {userId: userId, subject: like},
            success: function (data) {
//                console.log(data);
                if (data.status === true) {
                    if (action === 'addLike') {
                        elContext.addClass('active');
                    }
                    if (action === 'removeLike') {
                        elContext.removeClass('active');
                    }
                    elContext.siblings('span').text(data.value);
                }
            },
            error: function (data) {
//                console.log(data);
            }
        });
    }
}

var delay = (function () {
    var currentTime = 0;
    return function (callback, ms) {
        clearTimeout(currentTime);
        currentTime = setTimeout(callback, ms);
    };
})();

function passActions() {
    if ($(this).hasClass('hidden-pass')) {
        $(this).removeClass('hidden-pass');
        $(this).addClass('show-pass');
        $(this).siblings('input[type="password"]').attr('type', 'text');
    } else {
        if ($(this).hasClass('show-pass')) {
            $(this).removeClass('show-pass');
            $(this).addClass('hidden-pass');
            $(this).siblings('input[type="text"]').attr('type', 'password');
        }
    }
}

function mainSearchAjaxStart() {
    $(document).ajaxStart(function () {
        if (window.isThisMainSearch) {
            $('.main-menu-content-block > .loading').removeClass('hidden');
        }
    });
}

function mainSearchAjaxStop() {
    $(document).ajaxStop(function () {
        $('.main-menu-content-block > .loading').addClass('hidden');
        window.isThisMainSearch = false;
    });
}

// Обнуление значения в select-city

function setCityToDefault() {
    var select = $('#mainSearchForm p select[name="city"]');
    var selectBoxCont = select.siblings('.selectboxit-container');
    select.find('option[value="0"]').prop('selected', 'selected');
    select.select2();
}

// Отправка и получение данных для поиска на главной
window.isThisMainSearch = false;
function getMainSearchResult(data) {
//    console.log(data);
    window.isThisMainSearch = true;
    $.ajax({
        type: 'POST',
        url: '/users/getUsersByRating',
        data: data,
        success: function (data) {
//            console.log(data.length);
            addUsersToMainTable(data);
        },
        error: function (data) {

        }
    });
}

//Добавление пользователей в таблицу на главной странице
function addUsersToMainTable(data, new_data) {
    var table = $('#mainTablePeople');
    var emptyRes = $('#mainTablePeople tfoot .emptyResults');
    var head = table.find('thead');
    var body = table.find('tbody');
    if (new_data === undefined) {
        body.html('');
    }
    if (!$.isEmptyObject(data)) {

        emptyRes.addClass('hidden');
        var tr = {};
        var position = body.find(".js-link").length;
        $.each(data, function (index, value) {
            tr = head.find('tr.for-copy').clone();
            tr.removeClass('hidden for-copy');
            tr.attr('data-link', '/id' + value.id);
            tr.find('.mt-position').text(position + index + 1);

            if (value.name != null) {
                tr.find('.mt-name').find('span').text(value.name);
                if (value.verified === "2") {
                    tr.find('.mt-name').find('span').after('<i class="icon-checkmark color-blue has-title" data-title="Подтвержденная страница"></i>');
                }
            }
            
            var link = tr.find('a').attr('href');
            tr.find('a').attr('href', link + "/id" + value.id);
            
            if (value.avatar != null) {
                var img = tr.find('.mt-name').find('img');
                var src = img.data('src');
                img.attr('src', src + value.avatar);
                if (value.name != null) {
                    img.attr('alt', value.name);
                }
            } else {
                var img = tr.find('.mt-name').find('img');
                var src = img.data('src');
                img.attr('src', src + 'unknown.png');
                if (value.name != null) {
                    img.attr('alt', value.name);
                }
            }

            if (value.rating != null) {
                tr.find('.mt-rating').find('span').text(value.rating);
                tr.find('.mt-rating').append('<i class="rating-up"></i>');
            }

            if (value.activ_name != null) {
                tr.find('.mt-activity').text(value.activ_name);
            }

            if (value.country_name != null) {
                tr.find('.mt-country').text(value.country_name);
            }

            body.append(tr);

        });
    } else {
        if (!(body.find(".js-link").length > 0)) {
            emptyRes.removeClass('hidden');
        }
    }

}

// Получение городов по id страны
function getCities(countryId) {
    var data = {};
    if (countryId != 0) {
        data = {'countryId': countryId};
        //console.log(data);
        $.ajax({
            type: "POST",
            url: '/city/getCities',
            data: data,
            success: function (data) {
                addCitiesToSelect(data);
            },
            error: function (data) {
                console.log(data);
            }
        });
    } else {
        var citySelect = $('#mainSearchForm select[name="city"]');
        citySelect.attr('disabled', 'disabled');
        citySelect.find('option[value="0"]').attr('selected', true);
        citySelect.find('option:not(:first-child)').remove();
        var newSelect = citySelect.clone();
        newSelect.insertAfter(citySelect);
        citySelect.siblings('.select2').remove();
        newSelect.select2();
        citySelect.remove();
    }
}

function addCitiesToSelect(cities) {
    var citySelect = $('#mainSearchForm select[name="city"]');
    citySelect.find('option:not(:first-child)').remove();
    var newSelect = citySelect.clone();
    newSelect.append();
    $.each(cities, function (index, value) {
        newSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
    });
    newSelect.removeAttr('disabled');
    newSelect.insertAfter(citySelect);
    citySelect.siblings('.select2').remove();
    newSelect.select2();
    citySelect.remove();
}

var alreadyloading = false;
var nextpage = 2;
var context = '';
var difference = false;

$(window).scroll(function () {
    if ($('body').height() - (window.innerHeight * 0.7) <= (window.innerHeight + $(window).scrollTop())) {

        if (window.location.pathname == '/') {

            if (context != $('#mainSearchForm.active-search select').parents('#mainSearchForm').serialize()) {
                context = $('#mainSearchForm.active-search select').parents('#mainSearchForm').serialize();
                difference = true;
                nextpage = 2;
            } else {
                difference = false;
            }

            if ((alreadyloading == false) || difference) {
                alreadyloading = true;
                if (context != $('#mainSearchForm.active-search select').parents('#mainSearchForm').serialize()) {
                    context = $('#mainSearchForm.active-search select').parents('#mainSearchForm').serialize();
                    difference = true;
                } else {
                    difference = false;
                }
                var data = context + "&page=" + nextpage + "&per_page=20";

                $.ajax({
                    type: 'GET',
                    url: '/users/getUsersByRating',
                    data: data,
                    success: function (data) {
                        if (data.length > 0) {
                            addUsersToMainTable(data, true);
                            alreadyloading = false;
                            nextpage++;
                        } else {
                            alreadyloading = true;
                        }
                    },
                    error: function (data) {
                    }
                });

            }
            
        }

    }
});