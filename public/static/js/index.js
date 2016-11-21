/**
 * Created by admin on 2016/11/19.
 */
jQuery(function () {
    var tmpValue = {
        $loginBtn: $("#loginBtn"),
        $identifier: $("#identifier"),
        $warningTip: $("#warningTip"),
        $formLogin: $("#identifier form").eq(0),
        $searchBook: $(".bs-example").eq(0),
        $concreteBookMsg: $("#concreteBookMsg"),
        $loadingGif: $("#loadingGif"),
        $pagerLi: $(".pager li"),
        $sumBook: 0
    };

    (function () {
        if (sessionStorage.getItem("pageNum") != undefined) {
            if (sessionStorage.isLogin === 'false') {
                loadPage(Number(sessionStorage.getItem("pageNum")), function () {
                    document.body.scrollTop = Number(sessionStorage.pageTop);
                });
            }

        }
        sessionStorage.isLogin = 'false';
    })();


    /**
     *显示警告信息
     *
     * @method warningTipShow
     * @param t
     *
     */
    function warningTipShow(t) {
        var text = t || "";
        tmpValue.$warningTip.find("span").eq(1).text(text);
        tmpValue.$warningTip.slideDown(400, function () {
            setTimeout(function () {
                tmpValue.$warningTip.slideUp(400)
            }, 1500);
        });
    }

    tmpValue.$loginBtn.click(function () {
        /*
         if ((localStorage.number != undefined) && (document.cookie == localStorage.cookie)) {
         location.href = "/hometowm_book/Home/index/login.html";

         }
         */
        tmpValue.$identifier.modal("show");
    });

    tmpValue.$identifier.find("button").eq(0).click(function () {
        sessionStorage.isLogin = 'true';
    });


    /**
     *提交搜索书的信息
     *
     */
    tmpValue.$searchBook.submit(function (event) {
        var e = event || window.event;
        e.preventDefault();
        var inputBook = $("input[name='book']");
        var key_word = inputBook.val().trim();
        if (key_word == "") {
            inputBook.blur();
        } else {
            var pageNum = 0;
            localStorage.setItem("pageNum", pageNum);
            tmpValue.sumBook = 0;
            postSearchBook({
                key_word: key_word
            });
        }
    });


    /**
     * 发送检阅书目信息
     *
     * @method postSearchBook
     * @param config
     * @returns {null}
     */
    function postSearchBook(config) {
        var _config = config || {};
        var SEARCH_BOOK_URL = "/hometowm_book/home/search/search.html";
        tmpValue.$loadingGif.show();
        $.ajax({
            url: SEARCH_BOOK_URL,
            data: _config,
            success: function (data) {
                tmpValue.$loadingGif.hide();
                showBook(data);
            },
            error: function () {
                tmpValue.$loadingGif.hide();
                warningTipShow("加载失败");
            }
        });
        return null;
    }


    /**
     * 显示书的信息
     *
     * @method showBook
     * @param data
     * @returns {null}
     */
    function showBook(data) {
        var text = "";
        var cssNum = null;
        var bookNum = 0;
        var _data = JSON.parse(data);


        localStorage.setItem("page", _data[0]);
        sessionStorage.setItem("pageNum", localStorage.page);
        var moreItem = (sessionStorage.pageNum - 1) * 20;


        if (sessionStorage.sumBook == undefined) {
            sessionStorage.sumBook = 0;
        }
        $("#book .panel-title").text("已找到图书 " + _data[2] + " 本");


        $.each(_data[1], function (index, value) {
            if (value.book_r + "" == "0") {
                cssNum = "'danger'";
            } else {
                cssNum = "'success'";
            }
            tmpValue.$sumBook = index + moreItem + 1;
            var book_tp = value.book_tp || "空";
            text += "<li class='li-book'>" +
                "<table class='table table-condensed book-msg'>" +
                "<caption class='active'><a href='/hometowm_book/home/book_info/book_info.html?href_num=" + value.href_num + "'>" + tmpValue.$sumBook + "." + value.book_name + "</a></caption>" +
                "<tr>" + "<td>作者</td>" + "<td>" + value.book_a + "</td>" + "</tr>" +
                "<tr>" + "<td>出版社</td>" + "<td>" + value.book_p + "</td>" + "</tr>" +
                "<tr>" + "<td>索书号</td>" + "<td>" + book_tp + "</td>" + "</tr>" +
                "<tr class=" + cssNum + ">" + "<td>可借</td>" + "<td>" + value.book_r + "</td>" +
                "</tr>" + "</table>" + "</li>";
            bookNum = index;
        });


        $(".ul-book").html(text);

        document.body.scrollTop = 0;

        $(".book-msg a").click(function () {
            sessionStorage.pageTop = document.body.scrollTop;
        });

        $("#footer").show();

        tmpValue.$pagerLi.eq(1).html("<a href='javascript:void(0)'>第" + localStorage.page + "页</a>");
        if (localStorage.getItem("page") == 1) {
            tmpValue.$pagerLi.eq(0).addClass("hidden");
        } else {
            tmpValue.$pagerLi.eq(0).removeClass("hidden");
        }
        if (bookNum < 19) {
            tmpValue.$pagerLi.eq(2).addClass("hidden");
        } else {
            tmpValue.$pagerLi.eq(2).removeClass("hidden");
        }
        //判断是否还要加载数据
        return null;
    }

    tmpValue.$pagerLi.eq(0).click(function () {
        loadPage(Number(localStorage.getItem("page")) - 1, null);
    });

    tmpValue.$pagerLi.eq(2).click(function () {
        loadPage(Number(localStorage.getItem("page")) + 1, null);
    });


    /**
     * 加载的页面,需要到的页面
     * @method loadPage
     * @param page
     * @param callback
     *
     * @return null
     *
     */
    function loadPage(page, callback) {
        const URL = "/hometowm_book/home/search/search.html";
        tmpValue.$loadingGif.show();
        $.ajax({
            url: URL,
            data: {
                "page": page
            },
            success: function (data) {
                if(showBook(data) === null){
                    typeof callback === 'function' && callback();
                }
                tmpValue.$loadingGif.hide();
            },
            error: function () {
                warningTipShow("加载失败");
                tmpValue.$loadingGif.hide();
            }
        });

        return null;

    }

});