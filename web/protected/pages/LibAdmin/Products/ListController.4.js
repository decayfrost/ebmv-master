var PageJs = new Class.create();
PageJs.prototype = Object.extend(new FrontPageJs(), {
    htmlIDs : {
        totalCountDiv : "",
        listingDiv : "",
        showOrderBtn : "",
        orderSummaryDiv : "",
        showCartLink : ""
    },
    pagination : {
        pageNo : 1,
        pageSize : 30
    },
    order : {},
    searchCriteria : {},
    setHTMLIDs : function(e, d, c, a, b) {
        this.htmlIDs.totalCountDiv = e;
        this.htmlIDs.listingDiv = d;
        this.htmlIDs.orderSummaryDiv = c;
        this.htmlIDs.showOrderBtn = a;
        this.htmlIDs.showCartLink = b;
        return this
    },
    _orderProduct : function(b) {
        var a = {};
        a.me = this;
        a.btn = b;
        a.product = a.btn.up(".prodcut-row").retrieve("data");
        a.qty = $F(a.btn.up(".prodcut-row").down(".order-qty"));
        a.me.postAjax(a.me.getCallbackId("orderProduct"), {
            orderId : a.me.order.id,
            productId : a.product.id,
			unitPrice : a.product.price,
            qty : a.qty
        }, {
            onLoading : function() {
            },
            onComplete : function(c, f) {
                try {
                    a.result = a.me.getResp(f, false, true);
                    if (!a.result.order) {
                        return
                    }
                    a.me.order = a.result.order;
                    a.me._displayOrderSummary(a.me.order)
                } catch(d) {
                    a.me.showModalBox("ERROR", d, true)
                }
            }
        });
        return a.me
    },
    _getResultTableRow : function(c, a) {
        var b = {};
        b.me = this;
        b.isTitle = (a || false);
        b.tag = (b.isTitle === true ? "th" : "td");
        b.img = (b.isTitle === true ? "" : new Element("a", {
            href : "javascript: void(0);"
        }).update(c.img).observe("click", function() {
            b.src = $(this).down("img").readAttribute("src");
            jQuery.fancybox({
                type : "image",
                href : b.src,
                title : c.title
            })
        }));
        b.orderBtns = new Element("div", {
            "class" : "input-group input-group-sm"
        }).insert({
            bottom : new Element("input", {
                "class" : "form-control order-qty",
                type : "text",
                value : "1",
                style : "padding: 4px;"
            })
        }).insert({
            bottom : new Element("span", {
                "class" : "input-group-btn"
            }).insert({
                bottom : new Element("span", {
                    "class" : "btn btn-success"
                }).update(new Element("span", {
                    "class" : "glyphicon glyphicon-plus"
                })).observe("click", function() {
                    b.me._orderProduct(this)
                })
            })
        });
        b.Qty = new Element("div").insert({
            bottom : new Element("div", {
                "class" : "row"
            }).insert({
                bottom : new Element("strong", {
                    "class" : "col-xs-5"
                }).update("Has:")
            }).insert({
                bottom : new Element("div", {
                    "class" : "col-xs-3"
                }).update(c.qty)
            })
        }).insert({
            bottom : new Element("div", {
                "class" : "row",
                title : (c.orderedLibs ? c.orderedLibs.size() : "") + " libraries ordered this item"
            }).insert({
                bottom : new Element("strong", {
                    "class" : "col-xs-5"
                }).update("Libs:")
            }).insert({
                bottom : new Element("a", {
                    "class" : "col-xs-3",
                    href : "javascript: void(0);"
                }).update(c.orderedLibs ? c.orderedLibs.size() : "").observe("click", function() {
                    if (c.orderedLibs.size() === 0) {
                        return
                    }
                    b.div = new Element("div").insert({
                        bottom : b.list = new Element("div", {
                            "class" : "list-group",
                            style : "min-width: 400px;"
                        }).insert({
                            bottom : new Element("div", {
                                "class" : "list-group-item active"
                            }).update("Libraries that order this:" + c.title)
                        })
                    });
                    c.orderedLibs.each(function(d) {
                        b.list.insert({
                            bottom : new Element("div", {
                                "class" : "list-group-item"
                            }).update(d.name)
                        })
                    });
                    jQuery.fancybox({
                        type : "html",
                        content : b.div.innerHTML
                    })
                })
            })
        });
        b.row = new Element("tr", {
            "class" : "prodcut-row"
        }).store("data", c).insert({
            bottom : new Element(b.tag, {
                "class" : "col-sm-1"
            }).update(b.img)
        }).insert({
            bottom : new Element(b.tag).update(c.title)
        }).insert({
            bottom : new Element(b.tag, {
                "class" : "col-sm-2"
            }).update(c.price)
        }).insert({
            bottom : new Element(b.tag, {
                "class" : "col-sm-2"
            }).update(c.isbn)
        }).insert({
            bottom : new Element(b.tag, {
                "class" : "col-sm-1"
            }).update(c.author)
        }).insert({
            bottom : new Element(b.tag, {
                "class" : "col-sm-1"
            }).update(c.publishDate)
        }).insert({
            bottom : new Element(b.tag, {
                "class" : "col-sm-1"
            }).update(b.isTitle === true ? c.qty : b.Qty)
        }).insert({
            bottom : new Element(b.tag, {
                "class" : "col-sm-1"
            }).update(b.isTitle === true ? "" : b.orderBtns)
        });
        return b.row
    },
    _getLoadingDiv : function() {
        return new Element("span", {
            "class" : "loading"
        }).insert({
            bottom : new Element("img", {
                src : "/images/loading.gif"
            })
        }).insert({
            bottom : "Loading ..."
        })
    },
    _getPaginationDiv : function(a) {
        var b = {};
        if (a.pageNumber >= a.totalPages) {
            return
        }
        b.me = this;
        return new Element("div", {
            "class" : "pagination_wrapper pull-right"
        }).insert({
            bottom : b.me._getPaginationBtn("查看更多 / 查看更多<br />Get more", a.pageNumber + 1)
        })
    },
    changePage : function(d, b, a) {
        var c = {};
        this.pagination.pageNo = b;
        this.pagination.pageSize = a;
        $(d).update("Getting more ....").writeAttribute("disabled", true);
        this.getResult(false, function() {
            $(d).up(".pagination_wrapper").remove()
        })
    },
    _getPaginationBtn : function(a, b) {
        var c = {};
        c.me = this;
        return new Element("button", {
            "class" : "btn btn-primary",
            type : "button"
        }).update(a).observe("click", function() {
            c.me.changePage(this, b, c.me.pagination.pageSize)
        })
    },
    searchProducts : function(b) {
        var a = {};
        a.me = this;
        a.me.searchCriteria.searchTxt = b;
        a.me.getResult(true);
        return a.me
    },
    getResult : function(b, c) {
        var a = {};
        a.me = this;
        a.reset = (b || false);
        a.me.postAjax(a.me.getCallbackId("getItems"), {
            pagination : a.me.pagination,
            searchCriteria : a.me.searchCriteria
        }, {
            onLoading : function() {
                if (a.reset === true) {
                    a.me.pagination.pageNo = 1;
                    $(a.me.htmlIDs.listingDiv).update(a.me.getLoadingImg())
                }
            },
            onComplete : function(d, g) {
                try {
                    a.result = a.me.getResp(g, false, true);
                    if (!a.result.items) {
                        return
                    }
                    if (a.reset === true) {
                        $(a.me.htmlIDs.listingDiv).update(new Element("table", {
                            "class" : "table table-striped table-hover"
                        }).insert({
                            bottom : new Element("thead").update(a.me._getResultTableRow({
                                title : "Name",
                                price : "Price",
                                isbn : "ISBN",
                                qty : "Qty",
                                author : "Author",
                                publishDate : "Publish Date"
                            }, true))
                        }).insert({
                            bottom : a.tbody = new Element("tbody")
                        }));
                        $(a.me.htmlIDs.totalCountDiv).update(a.result.pagination.totalRows)
                    }
                    a.result.items.each(function(e) {
                        a.item = {
                            id : e.id,
                            title : e.title,
                            price : e.attributes.price ? ((e.attributes.price[0].attribute-0)*(100+(e.gpm-0))/100) : "",
                            isbn : e.attributes.isbn ? e.attributes.isbn[0].attribute : "",
                            img : a.me._getProductImgDiv(e.attributes.image_thumb || null, {
                                style : "height: 50px; width:auto;"
                            }),
                            author : e.attributes.author ? e.attributes.author[0].attribute : "",
                            publishDate : e.attributes.publish_date ? e.attributes.publish_date[0].attribute : "",
                            qty : e.orderedQty,
                            orderedLibs : e.orderedLibs
                        };
                        $(a.me.htmlIDs.listingDiv).down("tbody").insert({
                            bottom : a.me._getResultTableRow(a.item, false)
                        })
                    });
                    $(a.me.htmlIDs.listingDiv).insert({
                        bottom : a.me._getPaginationDiv(a.result.pagination)
                    })
                } catch(f) {
                    $(a.me.htmlIDs.listingDiv).update(a.me.getAlertBox("Error: ", f).addClassName("alert-danger"))
                }
                if ( typeof (c) === "function") {
                    c()
                }
            }
        });
        return a.me
    },
    _displayOrderSummary : function(a) {
        var b = {};
        b.me = this;
        $(b.me.htmlIDs.orderSummaryDiv).update("");
        a.items.reverse().each(function(c) {
            $(b.me.htmlIDs.orderSummaryDiv).insert({
                bottom : new Element("a", {
                    "class" : "list-group-item"
                }).insert({
                    bottom : c.product.title
                }).insert({
                    bottom : new Element("span", {
                        "class" : "badge"
                    }).update(c.qty)
                })
            })
        });
        return b.me
    },
    _openDetailsPage : function(b) {
        var a = {};
        a.me = this;
        jQuery.fancybox({
            width : "95%",
            height : "95%",
            autoScale : false,
            autoDimensions : false,
            fitToView : false,
            autoSize : false,
            type : "iframe",
            href : "/libadmin/order/" + b.id + ".html",
            beforeClose : function() {
                a.order = $$("iframe.fancybox-iframe").first().contentWindow.pageJs._order;
                if (a.order && a.order.status !== "OPEN") {
                    window.location = document.URL
                } else {
                    a.me.order = a.order;
                    a.me._displayOrderSummary(a.me.order)
                }
            }
        });
        return a.me
    },
    getOrderSummary : function() {
        var a = {};
        a.me = this;
        a.me.postAjax(a.me.getCallbackId("getOrderSummary"), {}, {
            onLoading : function() {
            },
            onComplete : function(b, d) {
                try {
                    a.result = a.me.getResp(d, false, true);
                    if (!a.result.order) {
                        return
                    }
                    a.me.order = a.result.order;
                    a.me._displayOrderSummary(a.me.order);
                    $(a.me.htmlIDs.showCartLink).observe("click", function() {
                        a.me._openDetailsPage(a.me.order)
                    });
                    $(a.me.htmlIDs.showOrderBtn).insert({
                        bottom : new Element("span", {
                            "class" : "btn btn-success btn-sm"
                        }).update("Checkout").observe("click", function() {
                            a.me._openDetailsPage(a.me.order)
                        })
                    })
                } catch(c) {
                    a.me.showModalBox("ERROR", c, true)
                }
            }
        });
        return a.me
    }
}); 