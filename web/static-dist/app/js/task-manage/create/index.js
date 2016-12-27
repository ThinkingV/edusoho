webpackJsonp(["app/js/task-manage/create/index"],{0:function(module,exports,__webpack_require__){"use strict";function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{"default":obj}}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor))throw new TypeError("Cannot call a class as a function")}var _createClass=function(){function defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||!1,descriptor.configurable=!0,"value"in descriptor&&(descriptor.writable=!0),Object.defineProperty(target,descriptor.key,descriptor)}}return function(Constructor,protoProps,staticProps){return protoProps&&defineProperties(Constructor.prototype,protoProps),staticProps&&defineProperties(Constructor,staticProps),Constructor}}(),_loadAnimation=__webpack_require__("b4fbf03f4f16003fe503"),_loadAnimation2=_interopRequireDefault(_loadAnimation);__webpack_require__("b3c50df5d8bf6315aeba");var _notify=__webpack_require__("b334fd7e4c5a19234db2"),_notify2=_interopRequireDefault(_notify),Editor=function(){function Editor($modal){_classCallCheck(this,Editor),this.$element=$modal,this.$task_manage_content=$("#task-create-content"),this.$task_manage_type=$("#task-create-type"),this.$frame=null,this.$iframe_body=null,this.iframe_jQuery=null,this.iframe_name="task-create-content-iframe",this.mode=this.$task_manage_type.data("editorMode"),this.type=this.$task_manage_type.data("editorType"),this.step=1,this.loaded=!1,this.contentUrl="",this._init(),this._initEvent()}return _createClass(Editor,[{key:"_initEvent",value:function(){var _this=this;$("#course-tasks-submit").click(function(event){return _this._onSave(event)}),$("#course-tasks-next").click(function(event){return _this._onNext(event)}),$("#course-tasks-prev").click(function(event){return _this._onPrev(event)}),"edit"!=this.mode?$(".js-course-tasks-item").click(function(event){return _this._onSetType(event)}):$(".delete-task").click(function(event){return _this._onDelete(event)})}},{key:"_init",value:function(){this._inItStep1form(),this._renderContent(this.step),"edit"==this.mode&&(this.contentUrl=this.$task_manage_type.data("editorStep2Url"),this.step=2,this._switchPage())}},{key:"_onNext",value:function(e){3!==this.step&&this._validator(this.step)&&(this.step+=1,this._switchPage())}},{key:"_onPrev",value:function(){1!==this.step&&this._validator(this.step)&&(this.step-=1,this._switchPage())}},{key:"_onSetType",value:function(event){var $this=$(event.currentTarget).addClass("active");$this.siblings().removeClass("active");var type=$this.data("type");$('[name="mediaType"]').val(type),this.contentUrl=$this.data("contentUrl"),this.type!==type?this.loaded=!1:this.loaded=!0,this.type=type,this._renderNext(!0)}},{key:"_onSave",value:function(event){var _this2=this;if(this._validator(this.step)){$(event.currentTarget).attr("disabled","disabled");var postData=$("#step1-form").serializeArray().concat(this.$iframe_body.find("#step2-form").serializeArray()).concat(this.$iframe_body.find("#step3-form").serializeArray());$.post(this.$task_manage_type.data("saveUrl"),postData).done(function(html){_this2.$element.modal("hide");var chapterId=postData.find(function(input){return"chapterId"==input.name}),add=0,$parent=$("#"+chapterId.value);$parent.length?($parent.nextAll().each(function(){return $(this).hasClass("task-manage-chapter")?($(this).before(html),add=1,!1):$parent.hasClass("task-manage-unit")&&$(this).hasClass("task-manage-unit")?($(this).before(html),add=1,!1):void 0}),1!=add&&($("#sortable-list").append(html),add=1)):$("#sortable-list").append(html);var data=$("#sortable-list").sortable("serialize").get();$.post($("#sortable-list").data("sortUrl"),{ids:data},function(response){})}).fail(function(response){_this2.$element.modal("hide")})}}},{key:"_onDelete",value:function(event){var _this3=this,$btn=$(event.currentTarget),url=$btn.data("url");void 0!==url&&$.post(url).then(function(response){(0,_notify2["default"])("success","删除成功"),_this3.$element.modal("hide"),document.location.reload()}).fail(function(error){(0,_notify2["default"])("warning","删除失败~~")})}},{key:"_switchPage",value:function(){this._renderStep(this.step),this._renderContent(this.step),this._rendStepIframe(this.step),this._rendButton(this.step),2!=this.step||this.loaded||this._initIframe()}},{key:"_initIframe",value:function(){var _this4=this,html='<iframe class="'+this.iframe_name+'" id="'+this.iframe_name+'" name="'+this.iframe_name+'" scrolling="no" src="'+this.contentUrl+'"></iframe>';this.$task_manage_content.html(html).show(),this.$frame=$("#"+this.iframe_name).iFrameResize();var loadiframe=function(){_this4.loaded=!0;var validator={};_this4.iframe_jQuery=_this4.$frame[0].contentWindow.$,_this4.$iframe_body=_this4.$frame.contents().find("body").addClass("task-iframe-body"),_this4._rendButton(2),_this4.$iframe_body.find("#step2-form").data("validator",validator),_this4.$iframe_body.find("#step3-form").data("validator",validator)};this.$frame.load((0,_loadAnimation2["default"])(loadiframe,this.$task_manage_content))}},{key:"_inItStep1form",value:function(){var $step1_form=$("#step1-form"),validator=$step1_form.validate({rules:{mediaType:{required:!0}},messages:{mediaType:"请选择%display%"}});$step1_form.data("validator",validator)}},{key:"_validator",value:function(step){var validator=null;if(1===step)validator=$("#step1-form").data("validator");else if(this.loaded){var $from=this.$iframe_body.find("#step"+step+"-form");validator=this.iframe_jQuery.data($from[0],"validator")}return!(validator&&!validator.form())}},{key:"_rendButton",value:function(step){if(1===step)this._renderPrev(!1),this._rendSubmit(!1),this._renderNext(!0);else if(2===step){if(this._renderPrev(!0),"edit"===this.mode&&this._renderPrev(!1),!this.loaded)return this._rendSubmit(!1),void this._renderNext(!1);this._rendSubmit(!0),this._renderNext(!0)}else 3===step&&(this._renderNext(!1),this._renderPrev(!0))}},{key:"_rendStepIframe",value:function(step){this.loaded&&(2===step?this.$iframe_body.find(".js-step2-view").addClass("active"):this.$iframe_body.find(".js-step2-view").removeClass("active"),3===step?this.$iframe_body.find(".js-step3-view").addClass("active"):this.$iframe_body.find(".js-step3-view").removeClass("active"))}},{key:"_renderStep",value:function(step){$("#task-create-step").find("li:eq("+(step-1)+")").addClass("doing").prev().addClass("done").removeClass("doing"),$("#task-create-step").find("li:eq("+(step-1)+")").next().removeClass("doing").removeClass("done")}},{key:"_renderContent",value:function(step){1===step?this.$task_manage_type.removeClass("hidden"):this.$task_manage_type.addClass("hidden"),1!==step?this.$task_manage_content.removeClass("hidden"):this.$task_manage_content.addClass("hidden")}},{key:"_renderNext",value:function(show){show?$("#course-tasks-next").removeClass("hidden").removeAttr("disabled"):$("#course-tasks-next").addClass("hidden")}},{key:"_renderPrev",value:function(show){show?$("#course-tasks-prev").removeClass("hidden"):$("#course-tasks-prev").addClass("hidden")}},{key:"_rendSubmit",value:function(show){show?$("#course-tasks-submit").removeClass("hidden"):$("#course-tasks-submit").addClass("hidden")}}]),Editor}();new Editor($("#modal"))},b4fbf03f4f16003fe503:function(module,exports){"use strict";Object.defineProperty(exports,"__esModule",{value:!0});var _arguments=arguments,loadAnimation=function(fn,$element){var $loding=$('<div class="load-animation"></div>');$loding.prependTo($element).nextAll().hide(),$element.append();var arr=[],l=fn.length;return function(x){return arr.push(x),$loding.hide().nextAll().show(),arr.length<l?_arguments.callee:fn.apply(null,arr)}};exports["default"]=loadAnimation}});