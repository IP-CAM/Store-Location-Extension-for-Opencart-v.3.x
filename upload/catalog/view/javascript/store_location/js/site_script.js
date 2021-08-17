/**
 * [asl_gdpr Enable the GDPR for the Store Locator]
 * @return {[type]} [description]
 */
var asl_gdpr = function() {

  window['asl_async_callback'] = function(){
    asl_store_locator();
    
  };

  /**
   * [load_google_maps Load the Google Maps Async]
   * @return {[type]} [description]
   */
  function load_google_maps() {

    var script  = document.createElement('script');
    script.type = 'text/javascript';
    script.src  = 'https://maps.googleapis.com/maps/api/js?libraries=places,drawing&callback=asl_async_callback&key=' + asl_configuration.api_key;
    document.body.appendChild(script);
  };
      
  
  /**
   * [gdpr_accepted Once user approve it]
   * @return {[type]} [description]
   */
  function gdpr_accepted() {

    window.localStorage && window.localStorage.setItem('asl-gdpr', 1);
    
    //  disable it once accepted
    asl_configuration.gdpr = false;

    load_google_maps();

    //  remove the GDPR box
    jQuery('.asl-p-cont #sl-btn-gdpr').parent().parent().remove();
  };


  //  Accept the GDPR
  jQuery('.asl-p-cont #sl-btn-gdpr').bind('click', gdpr_accepted);


  
  //  Check if already approved
  if(window.localStorage && window.localStorage.getItem('asl-gdpr') == '1') {
    gdpr_accepted();
  }
};

//  Call the GDPR
if(asl_configuration.gdpr == '1') {
  asl_gdpr();
}

/**
 * [asl_store_locator Store Locator Main Function]
 * @return {[type]} [description]
 */
function asl_store_locator() {

  //  when GDPR is enabled
  if(asl_configuration.gdpr == '1')return;

  if (!window['google'] || !google.maps) return;

  if (!window['_asl_map_customize']) {
    window['_asl_map_customize'] = null;
  }

  var asl_locator = function() {};

  window['asl_locator'] = asl_locator;

  
    //info box library
  function InfoBox(e){e=e||{};google.maps.OverlayView.apply(this,arguments);this.content_=e.content||"";this.disableAutoPan_=e.disableAutoPan||false;this.maxWidth_=e.maxWidth||0;this.pixelOffset_=e.pixelOffset||new google.maps.Size(0,0);this.position_=e.position||new google.maps.LatLng(0,0);this.zIndex_=e.zIndex||null;this.boxClass_=e.boxClass||"infoBox";this.boxStyle_=e.boxStyle||{};this.closeBoxMargin_=e.closeBoxMargin||"2px";this.closeBoxURL_=e.closeBoxURL||"https://www.google.com/intl/en_us/mapfiles/close.gif";if(e.closeBoxURL===""){this.closeBoxURL_=""}this.infoBoxClearance_=e.infoBoxClearance||new google.maps.Size(1,1);if(typeof e.visible==="undefined"){if(typeof e.isHidden==="undefined"){e.visible=true}else{e.visible=!e.isHidden}}this.isHidden_=!e.visible;this.alignBottom_=e.alignBottom||false;this.pane_=e.pane||"floatPane";this.enableEventPropagation_=e.enableEventPropagation||false;this.div_=null;this.closeListener_=null;this.moveListener_=null;this.contextListener_=null;this.eventListeners_=null;this.fixedWidthSet_=null}InfoBox.prototype=new google.maps.OverlayView;InfoBox.prototype.createInfoBoxDiv_=function(){var e;var t;var n;var r=this;var i=function(e){e.cancelBubble=true;if(e.stopPropagation){e.stopPropagation()}};var s=function(e){e.returnValue=false;if(e.preventDefault){e.preventDefault()}if(!r.enableEventPropagation_){i(e)}};if(!this.div_){this.div_=document.createElement("div");this.setBoxStyle_();if(typeof this.content_.nodeType==="undefined"){this.div_.innerHTML=this.getCloseBoxImg_()+this.content_}else{this.div_.innerHTML=this.getCloseBoxImg_();this.div_.appendChild(this.content_)}this.getPanes()[this.pane_].appendChild(this.div_);this.addClickHandler_();if(this.div_.style.width){this.fixedWidthSet_=true}else{if(this.maxWidth_!==0&&this.div_.offsetWidth>this.maxWidth_){this.div_.style.width=this.maxWidth_;this.div_.style.overflow="auto";this.fixedWidthSet_=true}else{n=this.getBoxWidths_();this.div_.style.width=this.div_.offsetWidth-n.left-n.right+"px";this.fixedWidthSet_=false}}this.panBox_(this.disableAutoPan_);if(!this.enableEventPropagation_){this.eventListeners_=[];t=["mousedown","mouseover","mouseout","mouseup","click","dblclick","touchstart","touchend","touchmove"];for(e=0;e<t.length;e++){this.eventListeners_.push(google.maps.event.addDomListener(this.div_,t[e],i))}this.eventListeners_.push(google.maps.event.addDomListener(this.div_,"mouseover",function(e){this.style.cursor="default"}))}this.contextListener_=google.maps.event.addDomListener(this.div_,"contextmenu",s);google.maps.event.trigger(this,"domready")}};InfoBox.prototype.getCloseBoxImg_=function(){var e="";if(this.closeBoxURL_!==""){e="<img";e+=" src='"+this.closeBoxURL_+"'";e+=" align=right";e+=" style='";e+=" position: relative;";e+=" cursor: pointer;";e+=" margin: "+this.closeBoxMargin_+";";e+="'>"}return e};InfoBox.prototype.addClickHandler_=function(){var e;if(this.closeBoxURL_!==""){e=this.div_.firstChild;this.closeListener_=google.maps.event.addDomListener(e,"click",this.getCloseClickHandler_())}else{this.closeListener_=null}};InfoBox.prototype.getCloseClickHandler_=function(){var e=this;return function(t){t.cancelBubble=true;if(t.stopPropagation){t.stopPropagation()}google.maps.event.trigger(e,"closeclick");e.close()}};InfoBox.prototype.panBox_=function(e){var t;var n;var r=0,i=0;if(!e){t=this.getMap();if(t instanceof google.maps.Map){if(!t.getBounds().contains(this.position_)){t.setCenter(this.position_)}n=t.getBounds();var s=t.getDiv();var o=s.offsetWidth;var u=s.offsetHeight;var a=this.pixelOffset_.width;var f=this.pixelOffset_.height;var l=this.div_.offsetWidth;var c=this.div_.offsetHeight;var h=this.infoBoxClearance_.width;var p=this.infoBoxClearance_.height;var d=this.getProjection().fromLatLngToContainerPixel(this.position_);if(d.x<-a+h){r=d.x+a-h}else if(d.x+l+a+h>o){r=d.x+l+a+h-o}if(this.alignBottom_){if(d.y<-f+p+c){i=d.y+f-p-c}else if(d.y+f+p>u){i=d.y+f+p-u}}else{if(d.y<-f+p){i=d.y+f-p}else if(d.y+c+f+p>u){i=d.y+c+f+p-u}}if(!(r===0&&i===0)){var v=t.getCenter();t.panBy(r,i)}}}};InfoBox.prototype.setBoxStyle_=function(){var e,t;if(this.div_){this.div_.className=this.boxClass_;this.div_.style.cssText="";t=this.boxStyle_;for(e in t){if(t.hasOwnProperty(e)){this.div_.style[e]=t[e]}}this.div_.style.WebkitTransform="translateZ(0)";if(typeof this.div_.style.opacity!=="undefined"&&this.div_.style.opacity!==""){this.div_.style.MsFilter='"progid:DXImageTransform.Microsoft.Alpha(Opacity='+this.div_.style.opacity*100+')"';this.div_.style.filter="alpha(opacity="+this.div_.style.opacity*100+")"}this.div_.style.position="absolute";this.div_.style.visibility="hidden";if(this.zIndex_!==null){this.div_.style.zIndex=this.zIndex_}}};InfoBox.prototype.getBoxWidths_=function(){var e;var t={top:0,bottom:0,left:0,right:0};var n=this.div_;if(document.defaultView&&document.defaultView.getComputedStyle){e=n.ownerDocument.defaultView.getComputedStyle(n,"");if(e){t.top=parseInt(e.borderTopWidth,10)||0;t.bottom=parseInt(e.borderBottomWidth,10)||0;t.left=parseInt(e.borderLeftWidth,10)||0;t.right=parseInt(e.borderRightWidth,10)||0}}else if(document.documentElement.currentStyle){if(n.currentStyle){t.top=parseInt(n.currentStyle.borderTopWidth,10)||0;t.bottom=parseInt(n.currentStyle.borderBottomWidth,10)||0;t.left=parseInt(n.currentStyle.borderLeftWidth,10)||0;t.right=parseInt(n.currentStyle.borderRightWidth,10)||0}}return t};InfoBox.prototype.onRemove=function(){if(this.div_){this.div_.parentNode.removeChild(this.div_);this.div_=null}};InfoBox.prototype.draw=function(){this.createInfoBoxDiv_();var e=this.getProjection().fromLatLngToDivPixel(this.position_);this.div_.style.left=e.x+this.pixelOffset_.width+"px";if(this.alignBottom_){this.div_.style.bottom=-(e.y+this.pixelOffset_.height)+"px"}else{this.div_.style.top=e.y+this.pixelOffset_.height+"px"}if(this.isHidden_){this.div_.style.visibility="hidden"}else{this.div_.style.visibility="visible"}};InfoBox.prototype.setOptions=function(e){if(typeof e.boxClass!=="undefined"){this.boxClass_=e.boxClass;this.setBoxStyle_()}if(typeof e.boxStyle!=="undefined"){this.boxStyle_=e.boxStyle;this.setBoxStyle_()}if(typeof e.content!=="undefined"){this.setContent(e.content)}if(typeof e.disableAutoPan!=="undefined"){this.disableAutoPan_=e.disableAutoPan}if(typeof e.maxWidth!=="undefined"){this.maxWidth_=e.maxWidth}if(typeof e.pixelOffset!=="undefined"){this.pixelOffset_=e.pixelOffset}if(typeof e.alignBottom!=="undefined"){this.alignBottom_=e.alignBottom}if(typeof e.position!=="undefined"){this.setPosition(e.position)}if(typeof e.zIndex!=="undefined"){this.setZIndex(e.zIndex)}if(typeof e.closeBoxMargin!=="undefined"){this.closeBoxMargin_=e.closeBoxMargin}if(typeof e.closeBoxURL!=="undefined"){this.closeBoxURL_=e.closeBoxURL}if(typeof e.infoBoxClearance!=="undefined"){this.infoBoxClearance_=e.infoBoxClearance}if(typeof e.isHidden!=="undefined"){this.isHidden_=e.isHidden}if(typeof e.visible!=="undefined"){this.isHidden_=!e.visible}if(typeof e.enableEventPropagation!=="undefined"){this.enableEventPropagation_=e.enableEventPropagation}if(this.div_){this.draw()}};InfoBox.prototype.setContent=function(e){this.content_=e;if(this.div_){if(this.closeListener_){google.maps.event.removeListener(this.closeListener_);this.closeListener_=null}if(!this.fixedWidthSet_){this.div_.style.width=""}if(typeof e.nodeType==="undefined"){this.div_.innerHTML=this.getCloseBoxImg_()+e}else{this.div_.innerHTML=this.getCloseBoxImg_();this.div_.appendChild(e)}if(!this.fixedWidthSet_){this.div_.style.width=this.div_.offsetWidth+"px";if(typeof e.nodeType==="undefined"){this.div_.innerHTML=this.getCloseBoxImg_()+e}else{this.div_.innerHTML=this.getCloseBoxImg_();this.div_.appendChild(e)}}this.addClickHandler_()}google.maps.event.trigger(this,"content_changed")};InfoBox.prototype.setPosition=function(e){this.position_=e;if(this.div_){this.draw()}google.maps.event.trigger(this,"position_changed")};InfoBox.prototype.setZIndex=function(e){this.zIndex_=e;if(this.div_){this.div_.style.zIndex=e}google.maps.event.trigger(this,"zindex_changed")};InfoBox.prototype.setVisible=function(e){this.isHidden_=!e;if(this.div_){this.div_.style.visibility=this.isHidden_?"hidden":"visible"}};InfoBox.prototype.getContent=function(){return this.content_};InfoBox.prototype.getPosition=function(){return this.position_};InfoBox.prototype.getZIndex=function(){return this.zIndex_};InfoBox.prototype.getVisible=function(){var e;if(typeof this.getMap()==="undefined"||this.getMap()===null){e=false}else{e=!this.isHidden_}return e};InfoBox.prototype.show=function(){this.isHidden_=false;if(this.div_){this.div_.style.visibility="visible"}};InfoBox.prototype.hide=function(){this.isHidden_=true;if(this.div_){this.div_.style.visibility="hidden"}};InfoBox.prototype.open=function(e,t){var n=this;if(t){this.position_=t.getPosition();this.moveListener_=google.maps.event.addListener(t,"position_changed",function(){n.setPosition(this.getPosition())})}this.setMap(e);if(this.div_){this.panBox_()}};InfoBox.prototype.close=function(){var e;if(this.closeListener_){google.maps.event.removeListener(this.closeListener_);this.closeListener_=null}if(this.eventListeners_){for(e=0;e<this.eventListeners_.length;e++){google.maps.event.removeListener(this.eventListeners_[e])}this.eventListeners_=null}if(this.moveListener_){google.maps.event.removeListener(this.moveListener_);this.moveListener_=null}if(this.contextListener_){google.maps.event.removeListener(this.contextListener_);this.contextListener_=null}this.setMap(null)};


  //mobile test
  function _isMobileDevice() {
    var check = false;
    (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
    return check;
  };


  /**
   * [addHours Add the hours to the Date]
   * @param {[type]} h [description]
   */
  Date.prototype.addHours = function(h) {
    this.setTime(this.getTime() + (h*60*60*1000));
    return this;
  };

  /**
   * [subDays description]
   * @param  {[type]} h [description]
   * @return {[type]}   [description]
   */
  Date.prototype.subDays = function(h) {
    this.setTime(this.getTime() - (h*60*60*1000 * 24));
    return this;
  };

  /**
   * [getDecimal Get the decimal of the number]
   * @return {[type]} [description]
   */
  function getDecimal(_value) {

      return Math.ceil((_value - Math.floor(_value)) * 100)
  };

  /**
   * [asl_drawing Code for ASL Drawing]
   * @type {Object}
   */
  var asl_drawing  = {
    shapes: [],
    shapes_index: 0,
    current_map: null,
    loadData: function(saved, _map) {

      var that = this;
      that.current_map = _map;

      for (var i in saved.shapes) {
        if (!saved.shapes[i]) continue;

        if (saved.shapes[i].type == 'polygon') {
          that.shapes.push(
            that.create_polygon.call(that, saved.shapes[i].coord, _map, saved.shapes[i])
          );
        } else if (saved.shapes[i].type == 'circle') {
          that.shapes.push(
            that.create_circle.call(that, saved.shapes[i], _map)
          );
        } else if (saved.shapes[i].type == 'rectangle') {
          that.shapes.push(
            that.create_rectangle.call(that, saved.shapes[i], _map)
          );
        }
      }
    },
    create_rectangle: function(_data) {

      var _map = this.current_map;

      return new google.maps.Rectangle({
        strokeColor: _data.strokeColor,
        fillColor: _data.color,
        strokeWeight: 1,
        type: 'rectangle',
        editable: (asl_drawing.allow_edit) ? asl_drawing.allow_edit : false,
        map: _map,
        bounds: new google.maps.LatLngBounds(
          new google.maps.LatLng(_data.sw[0], _data.sw[1]),
          new google.maps.LatLng(_data.ne[0], _data.ne[1]))
      });
    },
    create_circle: function(_data, _map) {

      var _map = this.current_map;

      return new google.maps.Circle({
        strokeColor: _data.strokeColor,
        fillColor: _data.color,
        type: 'circle',
        strokeWeight: 1,
        map: _map,
        editable: (asl_drawing.allow_edit) ? asl_drawing.allow_edit : false,
        center: new google.maps.LatLng(_data.center[0], _data.center[1]),
        radius: _data.radius
      });

    },
    create_polygon: function(_points, _map, _shape) {

      var _map = this.current_map;

      var coords = [];

      for (var i in _points) {

        coords.push({ lat: _points[i][0], lng: _points[i][1] });
      }

      return new google.maps.Polygon({
        paths: coords,
        fillColor: _shape.color,
        strokeColor: _shape.strokeColor,
        strokeWeight: 1,
        editable: (asl_drawing.allow_edit) ? true : false, // Editable geometry off by default
        type: 'polygon',
        map: _map
      });
    }
  },
  ASL_CLOSE_BUTTON = '';
  /*ASL_CLOSE_BUTTON = '<button class="asl-search-clr asl-clear-btn hide" type="button"><svg width="12" height="12" viewBox="0 0 12 12" xmlns="https://www.w3.org/2000/svg"><path d="M.566 1.698L0 1.13 1.132 0l.565.566L6 4.868 10.302.566 10.868 0 12 1.132l-.566.565L7.132 6l4.302 4.3.566.568L10.868 12l-.565-.566L6 7.132l-4.3 4.302L1.13 12 0 10.868l.566-.565L4.868 6 .566 1.698z"></path></svg></button>';*/

  /*ASL Script*/
  (function($) {


    //  Hack for the Template
    if (!$.templates) {
      $.templates = asl_jQuery.templates;
    }

    
    if (!$.views) {
        
      $.templates = asl_jQuery.views;
    }


    //  Enable Stars
    $.views.tags("stars", function(rating) {
      if(!isNaN(rating)) {
        rating = (Math.round(parseFloat(rating) * 4) / 4);
        var starPercentage        = (rating / 5) * 100;
        var starPercentageRounded = (Math.round(starPercentage / 5) * 5) + '%';
        return '<span class="sl-stars"><div class="sl-stars-out icon-star"><div style="width:' + starPercentageRounded+'" class="sl-stars-in icon-star"></div></div></span>';
      }
    });
    


    var asl_instance = {
      pan_mode: false,
    };


    if(!window['asl_markers']) {window['asl_markers'] = [];}

    
    /**
     * [add_prop_search description]
     * @param {[type]} _data         [description]
     * @param {[type]} _input        [description]
     * @param {[type]} _search_title [description]
     */
    asl_locator.add_prop_search = function(_data, _input, _search_title) {

      var taModel = $(_input),
        tdModel = new Bloodhound({
          datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
          queryTokenizer: Bloodhound.tokenizers.whitespace,
          local: _data
        });

      var taModelHandler = function(eventObject, obj, suggestionDataset) {


        //for address Search
        if (obj.type) {

          var bounds     = new google.maps.LatLngBounds();
          var all_stores = asl_view.data_.stores_;

          for (var _s in all_stores) {

            if (!all_stores.hasOwnProperty(_s)) continue;

          }

          //Delete it for Search Type :: 3
          asl_view._location = obj.title;

        

          if (asl_configuration.fit_bound) {
            asl_map.fitBounds(bounds);
          } 
          else {

            var bound_center = bounds.getCenter();
            asl_map.setCenter(bound_center);
            asl_map.setZoom(parseInt(asl_configuration.search_zoom));
          }  

          asl_view.refreshView();
          _input.next().removeClass('hide');
          return;
        }



        var ele_ = $('.sidebar-list .sidebar-list-item[data-id="' + obj.id + '"]');
        

        if (ele_[0]) {

          ele_.trigger('click').addClass('highlighted');


          if (asl_configuration.layout == '1') {

            ele_.parents('li').children(0).trigger('click');
          } 
          else
            $('#asl-storelocator #asl-list').animate({
              scrollTop: ele_.position().top
            }, 'fast');
        }
      };

      taModel.val('');


      //Enter Key
      taModel.bind('keydown', function(e) {

        
        if (e.which == 13) {

          if (taModel.parent().find('.tt-suggestion.tt-cursor')[0])
            taModel.parent().find('.tt-suggestion.tt-cursor').trigger('click');
          else
            taModel.parent().find('.tt-suggestion:first-child').trigger('click');

          
          //  Geocode it when Results are not found
          if(asl_configuration.additional_search && !taModel.parent().find('.tt-suggestion:first-child')[0] && e.currentTarget && e.currentTarget.value) {
            asl_view._panel.geoCoder(e.currentTarget);
          }
        }


      });

      

      //Value Changed :: RESET
      taModel.on('keyup', function(e) {

        if (this.id == 'asl-location-search' && !e.target.value && asl_view._location) {

          asl_view._location = null;
          asl_view.refreshView();
          asl_view._panel.stores_changed();
        }

      });

      //0 is Google Search
      if (asl_configuration.search_type != '0') {

        taModel.next().children().bind('click', function() {

          taModel.parent().find('.tt-suggestion:first-child').trigger('click');
        });
      }


      tdModel.initialize();

      taModel.typeahead({
        hint: false,
        highlight: true,
        minLength: 1
      }, {
        name: 'title',
        //limit: 10,
        displayKey: 'title',
        source: tdModel.ttAdapter()
      });

      taModel
      .on('typeahead:selected', taModelHandler);


    };

    //Save Analytics
    asl_locator.save_analytics = function(_store_or_loc, _viewed) {

    };

    asl_locator.toRad_ = function(degrees) {
      return degrees * Math.PI / 180;
    };

    asl_locator.Store = function(id, location, categories, props) {
      this.id_ = id;
      this.location_ = location;
      this.categories_ = categories /* || asl_locator.FeatureSet.NONE*/ ;
      this.props_ = props || {};
      this.v_id = props.vendor_id;
    };


    asl_locator.Store.prototype.setMarker = function(marker) {
      this.marker_ = marker;
      google.maps.event.trigger(this, 'marker_changed', marker);
    };

    /**
     * Gets this store's Marker
     * @return {google.maps.Marker} the store's marker.
     */
    asl_locator.Store.prototype.getMarker = function() {
      return this.marker_;
    };

    /**
     * Gets this store's ID.
     * @return {string} this store's ID.
     */
    asl_locator.Store.prototype.getId = function() {
      return this.id_;
    };

    /**
     * Gets this store's location.
     * @return {google.maps.LatLng} this store's location.
     */
    asl_locator.Store.prototype.getLocation = function() {
      return this.location_;
    };

    /**
     * Gets this store's features.
     * @return {asl_locator.FeatureSet} this store's features.
     */
    /*
    //Not used
    asl_locator.Store.prototype.getStoreFeatures = function() {
    return this.categories_;
    };
    */

    /**
     * Checks whether this store has a particular feature.
     * @param {!asl_locator.Feature} feature the feature to check for.
     * @return {boolean} true if the store has the feature, false otherwise.
     */
    asl_locator.Store.prototype.hasCategory = function(featureID) {

      return this.categories_.indexOf(featureID) != -1;
    };

    /**
     * Checks whether this store has all the given features.
     * @param {asl_locator.FeatureSet} features the features to check for.
     * @return {boolean} true if the store has all features, false otherwise.
     */
    asl_locator.Store.prototype.hasAnyCategory = function(active_features) {

      if (!active_features.array_.length) {
        return true;
      }


      var featureList = active_features.asList();

      for (var i = 0, ii = featureList.length; i < ii; i++) {

        if (this.categories_.indexOf(featureList[i].id_) != -1)
          return true;

        /*
          if (this.hasCategory(featureList[i].id_)) {
            return true;
          }
          */
      }

      return false;
    };

    /**
     * [hasAllCategory Checks whether this store has all the given features.]
     * @param  {[type]}  active_features [description]
     * @return {Boolean}                 [description]
     */
    asl_locator.Store.prototype.hasAllCategory = function(active_features) {

      if (!active_features.array_.length) {
        return true;
      }

      var featureList = active_features.asList();

      for (var i = 0, ii = featureList.length; i < ii; i++) {
        
        if (this.categories_.indexOf(featureList[i].id_) == -1) {
          return false;
        }
      }

      return true;
    };


    /**
     * Gets additional details about this store.
     * @return {Object} additional properties of this store.
     */
    asl_locator.Store.prototype.getDetails = function() {
      return this.props_;
    };

    /**
     * Generates HTML for additional details about this store.
     * @private
     * @param {Array.<string>} fields the optional fields of this store to output.
     * @return {string} html version of additional fields of this store.
     */
    asl_locator.Store.prototype.generateFieldsHTML_ = function(fields) {

      var html = [];

      for (var i = 0, ii = fields.length; i < ii; i++) {
        var prop = fields[i];


        if (this.props_[prop]) {
          html.push('<div class="');
          html.push(prop);
          html.push('">');
          html.push(prop + ': ');
          html.push((isNaN(this.props_[prop])) ? this.props_[prop] : numberWithCommas(this.props_[prop]));
          html.push('</div>');
        }
      }
      return html.join('');
    };

    /**
     * Generates a HTML list of this store's features.
     * @private
     * @return {string} html list of this store's features.
     */
    asl_locator.Store.prototype.generateFeaturesHTML_ = function() {

      var html = [];
      html.push('<ul class="features">');
      var featureList = this.categories_.asList();
      for (var i = 0, feature; feature = featureList[i]; i++) {
        html.push('<li>');
        html.push(feature.getDisplayName());
        html.push('</li>');
      }
      html.push('</ul>');
      return html.join('');
    };

    asl_locator.Store.prototype.getStoreContent = function() {

      if (!this.content_) {
        // TODO(cbro): make this a setting?

        //var tmpl = $.templates('#tmpl_list_item');
       // $.views.settings.delimiters("<%", "%>", "*");
       $.views.settings.delimiters("[%", "%]");
        var tmpl = (window['asl_tmpl_list_item']) ? window['asl_tmpl_list_item'] : $.templates((window['asl_info_list'] || '#tmpl_list_item'));
        window['asl_tmpl_list_item'] = tmpl;


        this.content_ = $(tmpl.render(this.props_));
        //html.push(this.generateFieldsHTML_(fields));
        //html.push(this.generateFeaturesHTML_());
      }
      return this.content_;
    };

    /**
     * Gets the HTML content for this Store, suitable for use in an InfoWindow.
     * @return {string} a HTML version of this store.
     */

    asl_locator.Store.prototype.getcontent_ = function(store) {
      $.views.settings.delimiters("[%", "%]");
      var tmpl = (window['asl_too_tip_tmpl']) ? window['asl_too_tip_tmpl'] : $.templates((window['asl_info_box'] || '#asl_too_tip'));
      window['asl_too_tip_tmpl'] = tmpl;

      store.props_.show_categories = asl_configuration.show_categories;
      store.props_.URL = asl_configuration.URL;

      var html_tip = tmpl.render(store.props_);
      return html_tip;
    };

    asl_locator.Store.prototype.getInfoWindowContent = function(stores) {

      var html = '<div class="infoWindow" id="style_' + ((asl_configuration.infobox_layout) ? asl_configuration.infobox_layout : '1') + '">';

      html += this.getcontent_(this);

      html += '</div>';

      this.content_ = html;
      return this.content_;
    };


    /**
     * Keep a cache of InfoPanel items (DOM Node), keyed by the store ID.
     * @private
     * @type {Object}
     */
    asl_locator.Store.infoPanelCache_ = {};

    /**
     * Gets a HTML element suitable for use in the InfoPanel.
     * @return {Node} a HTML element.
     */
    asl_locator.Store.prototype.getInfoPanelItem = function() {

      var store = this;
      var cache = asl_locator.Store.infoPanelCache_;
      var key = store.id_;



      if (!cache[key]) {
        var content = store.getStoreContent();
        cache[key] = content[0];

      }

      return cache[key];
    };

    /**
     * Gets the distance between this Store and a certain location.
     * @param {google.maps.LatLng} point the point to calculate distance to/from.
     * @return {number} the distance from this store to a given point.
     * @license
     *  Latitude/longitude spherical geodesy formulae & scripts
     *  (c) Chris Veness 2002-2010
     *  www.movable-type.co.uk/scripts/latlong.html
     */
    asl_locator.Store.prototype.distanceTo = function(point) {

      var R = 6371; // mean radius of earth
      var location = this.getLocation();
      var lat1 = asl_locator.toRad_(location.lat());
      var lon1 = asl_locator.toRad_(location.lng());
      var lat2 = asl_locator.toRad_(point.lat());
      var lon2 = asl_locator.toRad_(point.lng());
      var dLat = lat2 - lat1;
      var dLon = lon2 - lon1;
   
      var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1) * Math.cos(lat2) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      var _val = R * c;

      //return (asl_configuration.distance_unit == 'Miles') ? _val * 0.621371 : _val;
      return (asl_configuration.distance_unit == 'Km') ? _val * 0.621371 : _val;
    };


    ///////////////////////////////STORES END HERE///////////////////////////////
    asl_locator.View = function(map, data, opt_options) {

      this.map_ = map;
      this.data_ = data;


      this.settings_ = $.extend({
        'updateOnPan': true,
        'geolocation': true,
        'features': new asl_locator.FeatureSet
      }, opt_options);

      this.init_();

      google.maps.event.trigger(this, 'load');
      this.set('featureFilter', new asl_locator.FeatureSet);

      //  Active Marker
      if (asl_configuration.active_marker)
        this.active_marker = { m: null, picon: null, icon: new google.maps.MarkerImage(asl_configuration.URL + "icon/" + asl_configuration.active_marker, null, null) };
    };

    asl_locator['View'] = asl_locator.View;


    asl_locator.View.prototype = new google.maps.MVCObject;
    
    /*
      _no_refresh only works when user_center = 1
    */
    asl_locator.View.prototype.measure_distance = function(_latlng, _nearest, _no_refresh, _place_api) {

      var that = this;

      //  lOCALITY FILTER
      /*
      [
        {"long_name":"Sydney","short_name":"Sydney","types":["locality","political"]},
        {"long_name":"New South Wales","short_name":"NSW","types":["administrative_area_level_1","political"]},
        {"long_name":"Australia","short_name":"AU","types":["country","political"]},
        {"long_name":"2000","short_name":"2000","types":["postal_code"]}
      ]
      [
        {"long_name":"Denver","short_name":"Denver","types":["locality","political"]},
        {"long_name":"Denver County","short_name":"Denver County","types":["administrative_area_level_2","political"]},
        {"long_name":"Colorado","short_name":"CO","types":["administrative_area_level_1","political"]},
        {"long_name":"United States","short_name":"US","types":["country","political"]}
      ]
      */
      
      //  disable the sort random
      if(asl_configuration.sort_random)
        asl_configuration.sort_random = false;



      var loc = new google.maps.LatLng(_latlng.lat(), _latlng.lng());
      that._panel.dest_coords = that.dest_coords = loc;

      //var max_distance = asl_configuration.radius_range,
      var max_distance = 100,
        min_distance = 1000,
        nearest_pin = null;


      //get distance from current
      for (var s in that.data_.stores_) {

        if (!that.data_.stores_.hasOwnProperty(s)) continue;

        var _distance = that.data_.stores_[s].distanceTo(that.dest_coords);
        that.data_.stores_[s].content_ = null;


        //change KM TO Miles
        that.data_.stores_[s].props_.distance = _distance;
        that.data_.stores_[s].props_.dist_str = _distance.toFixed(2) + ' ' + asl_configuration.distance_unit;
        
 


        //console.log(that.data_.stores_[s].props_.city,'     ',that.data_.stores_[s].props_.state,'   ',that.data_.stores_[s].props_.postal_code);

        if (_distance > max_distance)
          max_distance = _distance;

        if (_distance < min_distance) {

          nearest_pin = that.data_.stores_[s];
          min_distance = _distance;
        }
      }

      //disable it in case of radius_range fixed
      //asl_configuration.radius_range = Math.round(max_distance);
      asl_configuration.radius_range = (asl_configuration.fixed_radius) ? parseInt(asl_configuration.fixed_radius) : Math.ceil(max_distance);


      $('.asl-p-cont #asl-radius-input').html(asl_configuration.radius_range);

      delete asl_locator.Store.infoPanelCache_;
      asl_locator.Store.infoPanelCache_ = {};

      //my marker
      if (!that.my_marker) {

        that.my_marker = new google.maps.Marker({
          title: asl_configuration.words.your_cur_loc,
          position: loc,
          zIndex: 0,
          animation: google.maps.Animation.DROP,
          draggable: true,
          map: (asl_configuration.geo_marker == '0')? null: that.getMap()
        });

        var marker_icon = new google.maps.MarkerImage(asl_configuration.URL + "icon/me-pin.png", null, null, null /*, new google.maps.Size(24,46)*/ );
        that.my_marker.setIcon(marker_icon);

        that.my_marker.addListener('dragend', function(_event) {

          that.measure_distance(_event.latLng);
        });
      } else
        that.my_marker.setPosition(loc);



      //Show the Nearest Store
      if (_nearest && asl_configuration.search_destin == '1' && nearest_pin) {

        loc = nearest_pin.getLocation();
      }
      


      /**
       * [radius_circle Create a Circle]
       * @param  {[type]} _location [description]
       * @return {[type]}           [description]
       */
      function radius_circle(_location) {

        var _radius = null;

        _radius = ((!asl_configuration.distance_slider || !asl_configuration.advance_filter) && !asl_configuration.fixed_radius) ? 100 : parseInt(asl_configuration.radius_range);

        //Change to KM
        _radius = _radius * 1000;

        //  Change Miles to KM Unit
        if (asl_configuration.distance_unit != 'KM') {

          _radius = _radius * 1.60934;
        }

        if (!that.$circle) {

          //  Create the Circle Control
          function circleStatus(controlDiv, map) {

            // Set CSS for the control border.
            $reset_btn = $('<div class="asl-radius-cnt"><div class="checkbox"><label for="asl-rad-circ"><input id="asl-rad-circ" checked type="checkbox">' + asl_configuration.words.radius_circle + '</label></div></div>');
            controlDiv.appendChild($reset_btn[0]);

            // Setup the click event listeners: simply set the map to Chicago.
            $reset_btn.find('#asl-rad-circ')[0].addEventListener('click', function() {

              that.$circle.setMap((this.checked) ? that.getMap() : null);
            });
          };

          var c_radiusDiv = document.createElement('div'),
            c_radius      = new circleStatus(c_radiusDiv, that.getMap());

          c_radiusDiv.index = 1;
          that.getMap().controls[google.maps.ControlPosition.RIGHT].push(c_radiusDiv);

          //  Add the Circle Overlay
          that.$circle = new google.maps.Circle({
            strokeColor: asl_configuration.radius_color || '#FFFF00',
            strokeOpacity: 0.7,
            strokeWeight: 2,
            fillColor: asl_configuration.radius_color || '#FFFF00',
            fillOpacity: 0.20,
            map: that.getMap(),
            radius: _radius,
            center: _location
          });
        } 
        else {

          //  Change it
          that.$circle.setOptions({ radius: _radius, center: _location });
        }

        //  Make the Circle Radius
        that.getMap().fitBounds(that.$circle.getBounds());
      };


      //  Radius Circle
      if (asl_configuration.radius_circle) {

        radius_circle(that.dest_coords);
        that.refreshView(true);
      } 
      else if (!_no_refresh) {

        that.getMap().setCenter(loc);
        that.getMap().setZoom((asl_configuration.search_zoom) ? parseInt(asl_configuration.search_zoom) : parseInt(asl_configuration.zoom));
        google.maps.event.trigger(that, 'load');

        that.refreshView(true);
      }
    };
    
    
     asl_locator.View.prototype.getMyPossition = function() {
         
     navigator.geolocation.getCurrentPosition(function(position) {
      var lat = position.coords.latitude;
          var lng = position.coords.longitude;
          var origin = lat , lng; 
          return origin;
});
         
         /*if (navigator.geolocation) {
         navigator.geolocation.getCurrentPosition(function(position){
         var lat = position.coords.latitude;
          var lng = position.coords.longitude;
          var origin = lat , lng; 
         });
          return origin;
        };*/
        // return origin;
        };


    /**
     * [reset_measure Reset the Measure]
     * @param  {[type]} _input [description]
     * @return {[type]}        [description]
     */
    asl_locator.View.prototype.reset_measure = function(_input) {

      var that = this;

      that._panel.dest_coords = that.dest_coords = null;

      var max_distance  = asl_configuration.radius_range,
        min_distance    = 1000,
        nearest_pin     = null;


      //  Get distance from current
      for (var s in that.data_.stores_) {

        if (!that.data_.stores_.hasOwnProperty(s)) continue;

        that.data_.stores_[s].content_ = null;

        // Change KM TO Miles
        that.data_.stores_[s].props_.dist_str = that.data_.stores_[s].props_.distance = null;
      }

      //disable it in case of radius_range fixed
      //asl_configuration.radius_range = Math.round(max_distance);
      asl_configuration.radius_range = (asl_configuration.fixed_radius) ? parseInt(asl_configuration.fixed_radius) : Math.round(max_distance);


      $('.asl-p-cont #asl-radius-input').html(asl_configuration.radius_range);

      delete asl_locator.Store.infoPanelCache_;


      asl_locator.Store.infoPanelCache_ = {};

      //  Clear the Marker
      if (that.my_marker) {

        that.my_marker.setMap(null);
        delete that.my_marker;
        that.my_marker = null;
      }

      //  Clear the Circle
      if(that.$circle) {

        that.$circle.setMap(null);
        delete that.$circle;
        that.$circle = null;
      }



      that.refreshView(true);

      $(_input).val('');
    };

    /**
     * [geolocate_ GeoLocate]
     * @return {[type]} [description]
     */
    asl_locator.View.prototype.geolocate_ = function() {

      var that = this;

      if (window.navigator && navigator.geolocation) {

        navigator.geolocation.getCurrentPosition(function(pos) {
            //that.getMyPossition(pos);
            that.measure_distance(new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude));
          },
          function(error) {

            var $err_msg = $('<div class="alert alert-danger asl-geo-err"></div>');

            switch (error.code) {

              case error.PERMISSION_DENIED:
                $err_msg.html((asl_configuration.words.geo_location_error || error.message) || "User denied the request for Geolocation.");
                break;
              case error.POSITION_UNAVAILABLE:
                $err_msg.html("Location information is unavailable.");
                break;
              case error.TIMEOUT:
                $err_msg.html("The request to get user location timed out.");
                break;
              case error.UNKNOWN_ERROR:
                $err_msg.html("An unknown error occurred.");
                break;
              default:
                $err_msg.html(error.message);
            }

            $err_msg.appendTo('.asl-p-cont .asl-map');
            window.setTimeout(function() {
              $err_msg.remove();
            }, 5000);

          }, ({
            maximumAge: 60 * 1000,
            timeout: 10 * 1000
          })
        ) /*()*/ ;
      }
    };


    /**
     * [fitBound Fit Bound to the Stores, asl_configuration.category_bound]
     * @return {[type]} [description]
     */
    asl_locator.View.prototype.fitBound = function(_stores) {

      var stores = _stores || this.get('stores');

      if(stores.length) {

        var bounds = new google.maps.LatLngBounds();

        for (var s in stores) {

          if (!stores.hasOwnProperty(s)) continue;

          bounds.extend(stores[s].getLocation());

        }

        this.getMap().fitBounds(bounds);
      }
    };




    /**
     * [init_ View Init]
     * @return {[type]} [description]
     */
    asl_locator.View.prototype.init_ = function() {

      //If geolocation is enabled show distance
      if (this.settings_['geolocation']) {
        this.geolocate_();
      }

      this.markerCache_ = {};

      var infobox_width = asl_configuration.infobox_width || 300;


     /// var closeURL = (asl_configuration.infobox_layout == '1') ? asl_configuration.PLUGIN_URL + "public/img/cross.png" : 'https://www.google.com/intl/en_us/mapfiles/close.gif';


     closeURL = 'https://www.google.com/intl/en_us/mapfiles/close.gif';

      this.infoWindow_ = new InfoBox({
        //disableAutoPan: false,
        //pixelOffset: new google.maps.Size(-65, -140),
        boxStyle: {
          //width: "250px",
          //margin:'0 0 33px -120px'
          width: infobox_width + "px",
          margin: '0 0 '+asl_configuration.marker_height+'px -' + ((infobox_width / 2)) + 'px'
        },
        alignBottom: true,
        pane: false,
        disableAutoPan: true,
        closeBoxMargin: (asl_configuration.infobox_layout != '1')? "8px 4px -21px 0px": "9px 12px -16px -36px",
        closeBoxURL: closeURL,
        infoBoxClearance: new google.maps.Size(1, 1)
      });

      //infoBox,0 0 15px 27px
      var that = this;
      var map = this.getMap();

      this.set('updateOnPan', this.settings_['updateOnPan']); // true or false

      google.maps.event.addListener(this.infoWindow_, 'closeclick', function() {
        that.highlight(null);
      });

      google.maps.event.addListener(map, 'click', function() {
        that.highlight(null);
        that.infoWindow_.close();
      });
    };


    /**
     * [load_complete Last Event, loading is completed]
     * @return {[type]} [description]
     */
    asl_locator.View.prototype.load_complete = function() {
      
    };

    /**
     * Adds/remove hooks as appropriate.
     */
    asl_locator.View.prototype.updateOnPan_changed = function() {


      if (this.updateOnPanListener_) {
        google.maps.event.removeListener(this.updateOnPanListener_);
      }

      var that = this;
      //validate the view

      if (this.get('updateOnPan') && this.getMap()) {
        var that = this,
          map = this.getMap(),
          updateOnPan_method = function(e) {

            if (asl_configuration.reset_button && $('.asl-reset-map')[0]) {

              if ($('.asl-reset-map')[0].style != 'block')
                $('.asl-reset-map')[0].style.display = 'block';
            }

            //if(that.showing_direction || asl_configuration.load_all != '1')return;
            //closing this refreshview for testing :: 4-22-2017 :: majin
            
            if(asl_configuration.sort_by_bound) {

              that.refreshView();
              that._panel.stores_changed();
            }
          };
        this.updateOnPanListener_ = google.maps.event.addListener(map, 'dragend', //idle
          updateOnPan_method
        );
      }
    };

    /**
     * Add a store to the map.
     * @param {asl_locator.Store} store the store to add.
     */
    asl_locator.View.prototype.addStoreToMap = function(store) {

      var marker = this.getMarker(store);
      store.setMarker(marker);
      var that = this;

      //Marker Click or mouse over event

      marker.clickListener_ = google.maps.event.addListener(marker, (asl_configuration.mouseover) ? 'mouseover' : 'click',
        function() {

          that.marker_clicked = true;
          that.marker_center = marker.getPosition();
          that.highlight(store, true);

          if (_asl_map_customize && _asl_map_customize.marker_animations == 1)
            marker.setAnimation(google.maps.Animation.Xp);
        });

      if (marker.getMap() != this.getMap()) {
        marker.setMap(this.getMap());

        if (_asl_map_customize && _asl_map_customize.marker_animations == 1)
          marker.setAnimation(google.maps.Animation.Xp);
      }
    };

    /**
     * Create a marker for a store.
     * @param {asl_locator.Store} store the store to produce a marker for.
     * @this asl_locator.View
     * @return {google.maps.Marker} a new marker.
     * @export
     */
    asl_locator.View.prototype.createMarker = function(store) {

      var markerSize = null;
      var url = asl_configuration.URL + 'icon/';


      //category marker
      if (asl_configuration.category_marker && store.categories_.length >= 1) {

        markerSize = (asl_configuration.template == 2 && asl_configuration.category_marker)? new google.maps.Size(30, 30) : null;

        url = asl_configuration.URL + 'svg/';
        //url += 'cmark-'+asl_categories[store.categories_[0]].id+'.png';
        url += (asl_categories[store.categories_[0]]) ? (asl_categories[store.categories_[0]].icon || 'default.png') : 'default.png';
      } 
      else
        url += (asl_markers[store.props_.marker_id]) ? (asl_markers[store.props_.marker_id].icon || 'default.png') : 'default.png';


      var marker_title = (asl_categories[store.categories_[0]] && asl_categories[store.categories_[0]]['name'])?  asl_categories[store.categories_[0]]['name'] + ' | ' + store.props_.title: store.props_.title;

        return new google.maps.Marker({
        title: marker_title,
        position: store.getLocation(),
        zIndex: store.props_.ordr || 0,
        animation: (_asl_map_customize && _asl_map_customize.marker_animations == 1) ? google.maps.Animation.BOUNCE : null,
        icon: new google.maps.MarkerImage(url, null, null,
          //  null)
          null, markerSize)
      });
      //null, (asl_configuration.category_marker)?markerSize:null)
    };

    /**
     * Get a marker for a store. By default, this caches the value from
     * createMarker(store)
     * @param {asl_locator.Store} store the store to get the marker from.
     * @return {google.maps.Marker} the marker.
     */
    asl_locator.View.prototype.getMarker = function(store) {

      var cache = this.markerCache_;
      var key = store.id_;

      if (!cache[key]) {

        cache[key] = this['createMarker'](store);
      }


      return cache[key];
    };

    /**
     * Get a InfoWindow for a particular store.
     * @param {asl_locator.Store} store the store.
     * @return {google.maps.InfoWindow} the store's InfoWindow.
     */
    asl_locator.View.prototype.getInfoWindow = function(store, stores) {

      if (!store) {
        return this.infoWindow_;
      }

      var div = $(store.getInfoWindowContent(stores));

      this.infoWindow_.setContent(div[0]);
      return this.infoWindow_;
    };

    /**
     * Gets all possible features for this View.
     * @return {asl_locator.FeatureSet} All possible features.
     */
    asl_locator.View.prototype.getViewFeatures = function() {

      return this.settings_['features'];
    };

    /**
     * Gets a feature by its id. Convenience method.
     * @param {string} id the feature's id.
     * @return {asl_locator.Feature|undefined} The feature, if the id is valid.
     * undefined if not.
     */
    asl_locator.View.prototype.getFeatureById = function(id) {

      if (!this.featureById_) {
        this.featureById_ = {};
        for (var i = 0, feature; feature = this.settings_['features'][i]; i++) {
          this.featureById_[feature.id_] = feature;
        }
      }
      return this.featureById_[id];
    };

    /**
     * featureFilter_changed event handler.
     */
    asl_locator.View.prototype.featureFilter_changed = function() {

      google.maps.event.trigger(this, 'featureFilter_changed',
        this.get('featureFilter'));

      if (this.get('stores')) {
        this.clearMarkers();
      }
    };

    /**
     * Clears the visible markers on the map.
     */
    asl_locator.View.prototype.clearMarkers = function() {

      for (var marker in this.markerCache_) {
        this.markerCache_[marker].setMap(null);
        var listener = this.markerCache_[marker].clickListener_;
        if (listener) {
          google.maps.event.removeListener(listener);
        }
      }
    };


    /**
     * [storesWithCategory Get the Stores with the Category ID]
     * @param  {[type]} _category_id [description]
     * @return {[type]}              [description]
     */
    asl_locator.View.prototype.storesWithCategory = function(_category_id) {

      var stores = this.get('stores');

      var filtered = [];

      if (stores) {

        for (var i = 0, ii = stores.length; i < ii; i++) {
          
          if(stores[i].hasCategory(_category_id)) {

            filtered.push(stores[i]);
          }
        }
      }

      return filtered;
    }


    /**
     * [bounce Bounce the Store Marker]
     * @param  {[type]} store   [description]
     * @param  {[type]} _status [description]
     * @return {[type]}         [description]
     */
    asl_locator.View.prototype.doBounce = function(store, _status) {

      var _marker = store.getMarker();

      _marker.setAnimation(null);

      if (_status) {
        //_marker.setZIndex(100);
        _marker.setAnimation(google.maps.Animation.BOUNCE);
      } else {
        //_marker.setZIndex(store.props_.ordr || 0);
      }

    };

    /**
     * Refresh the map's view. This will fetch new data based on the map's bounds.
     */
    asl_locator.View.prototype.refreshView = function(_clicked) {

      var that = this,
        bounds = this.getMap().getBounds();

      console.log('CALLING REFRESH VIEW');

      //  1- brand-attr
      var attr_fields = {};

      if(asl_configuration.filter_ddl) {

  
        for(var f in asl_configuration.filter_ddl) {

          if (!asl_configuration.filter_ddl.hasOwnProperty(f)) continue;

          var attr_name           = asl_configuration.filter_ddl[f];
          attr_fields[attr_name]  = asl_engine.controls[attr_name].val();

          if (asl_configuration.single_cat_select == '1') {
            attr_fields[attr_name] = (attr_fields[attr_name] == '0') ? null : [attr_fields[attr_name]];
          }
         // $("#categories_filter").append('<select id="asl-categories" ' + attr_name + ' style="width:350px"></select>');
           //  console.log(attr_name);


        }
      }


      this.data_.getStores(bounds, (this.get('featureFilter')), function(stores) {

        var oldStores = that.get('stores');

        //  Remove all old Stores Markers
        if (oldStores) {

          for (var i = 0, ii = oldStores.length; i < ii; i++) {
            google.maps.event.removeListener(
              oldStores[i].getMarker().clickListener_);
          }
        }


        //  When Range slider is enabled
        var stores_in_range = [],
            priority_stores = [],
            filter_text     = (that.filter_text)? that.filter_text: null,
          //unfiltered_stores   = [],
          check_distance = (asl_configuration.distance_slider && stores && stores[0] && stores[0].props_.dist_str) ? true : false;



        //Refresh Category Stores Counts
        var c_keys = Object.keys(asl_categories);

        //  2- brand-attr
        var fields = asl_configuration.filter_ddl;

        for (var c in c_keys) {

          if (asl_categories[c_keys[c]])
            asl_categories[c_keys[c]].len = 0;
        }


        for (var c in c_keys) {

          if (asl_categories[c_keys[c]])
            asl_categories[c_keys[c]].len = 0;
        }

        for (var k in stores) {

          if (!stores.hasOwnProperty(k)) continue;


          if (asl_configuration.fixed_radius && stores[k].props_.distance) {

            //If in Range
            if (stores[k].props_.distance >= asl_configuration.fixed_radius) {
              continue;
            }
          }


          //else add into array
          if (stores[k].props_.ordr > 0)
            priority_stores.push(stores[k]);
          else
            stores_in_range.push(stores[k]);
        }


       

        //  IF Priority Stores Exist, Sort them ?
        if (priority_stores.length > 0) {

          that.data_.sortByDesc('ordr', priority_stores);
        }

        
        ///////////////////
        /////// Sortings //
        ///////////////////
        
        if(asl_configuration.sort_random) {
          
          that.data_.sortRandom(stores_in_range);
        }
        else if (asl_configuration.sort_by) {

          that.data_.sortBy(asl_configuration.sort_by, stores_in_range);
        }
        else if (stores_in_range && that.dest_coords) {

          //  located Position
          that.data_.sortDistance(that.dest_coords, stores_in_range);
        } 
        else if (bounds && asl_configuration.sort_by_bound)
          that.data_.sortDistance(bounds.getCenter(), stores_in_range);


        //  Merge Priority Stores
        if (!asl_configuration.sort_random && priority_stores.length > 0) {

          stores_in_range = priority_stores.concat(stores_in_range);
        }


        //  Apply the Limit
        if (asl_configuration.stores_limit) {

          stores_in_range = stores_in_range.slice(0, asl_configuration.stores_limit);
        }


        //  FIT TO BOUND TO RESULT
        /*
          if(asl_configuration.filter_address && that.locality)  {

            var bounds = new google.maps.LatLngBounds();

                for(var _s in stores_in_range) {

                  if(!stores_in_range.hasOwnProperty(_s))continue;

                  bounds.extend(stores_in_range[_s].getLocation());
                }

                that.getMap().fitBounds(bounds);
          }
          */

        //  Jaysukh
        /*if(stores_in_range.length == 0) {
          stores_in_range = unfiltered_stores.splice(0,2);
        }*/

        //  save new stores          
        that.set('stores', stores_in_range);
      }, _clicked);
      ////console.log(this.data_);
    };
    
    

    /**
     * stores_changed event handler.
     * This will display all new stores on the map.
     * @this asl_locator.View
     */
    asl_locator.View.prototype.stores_changed = function() {


      var stores = this.get('stores'),
        current_markers = [];

      //clear Old Markers When Cluster is OFF
      if (asl_configuration.cluster != '1')
        this.clearMarkers();

      for (var i = 0, store; store = stores[i]; i++) {

        this.addStoreToMap(store);
        current_markers.push(store.marker_);
      }

      if (asl_configuration.cluster == '1') {

        asl_locator.marker_clusters.clearMarkers();
        asl_locator.marker_clusters.addMarkers(current_markers);
      }


      //stores change event
      if (asl_configuration.load_all != '1') {
        this._panel.stores_changed();
      }

      /*window.setTimeout(function(){
        //window['asl_panel'].stores_changed();
      },100);*/

    };

    /**
     * Gets the view's Map.
     * @return {google.maps.Map} the view's Map.
     */
    asl_locator.View.prototype.getMap = function() {
      return this.map_;
    };

    /**
     * [map_recenter Recenter the Map]
     * @param  {[type]} latlng  [description]
     * @param  {[type]} offsetx [description]
     * @param  {[type]} offsety [description]
     * @return {[type]}         [description]
     */
    asl_locator.View.prototype.map_recenter = function(latlng, offsetx, offsety) {

      var map = this.getMap();
      var point1 = map.getProjection().fromLatLngToPoint(
        (latlng instanceof google.maps.LatLng) ? latlng : map.getCenter()
      );
      var point2 = new google.maps.Point(
        ((typeof(offsetx) == 'number' ? offsetx : 0) / Math.pow(2, map.getZoom())) || 0,
        ((typeof(offsety) == 'number' ? offsety : 0) / Math.pow(2, map.getZoom())) || 0
      );
      map.panTo(map.getProjection().fromPointToLatLng(new google.maps.Point(
        point1.x - point2.x,
        point1.y + point2.y
      )));
    };

    /**
     * Select a particular store.
     * @param {asl_locator.Store} store the store to highlight.
     * @param {boolean} pan if panning to the store on the map is desired.
     */
    asl_locator.View.prototype.highlight = function(store, _item_clicked) {

      var infoWindow = null,
        _map = this.getMap();

      //close for mobile optimize
      /*
      if(asl_configuration.mobile_optimize && this.current_m) {

        this.current_m.setMap(null);
        this.current_m = null;
      }
      */

      //If valid Store
      if (store) {

        var stores = this.get('stores');
        infoWindow = this.getInfoWindow(store, stores);
        

        //  Bring the Scroll to Item
        if (!_item_clicked) {

          var ele_ = $('sidebar-list .sidebar-list-item[data-id="' + store.id_ + '"]');
          
        
          if (ele_[0])
            $('#asl-storelocator #asl-list').animate({
              scrollTop: ele_.position().top
            }, 'fast');
       
        }
     


        if (store.getMarker()) {

          var _this = this,
            m = store.getMarker();

          infoWindow.open(_map, store.getMarker());

          //Store viewed
          if (asl_configuration.analytics) {

            asl_locator.save_analytics(store, 1);
          }
        }

        /*
          else if(asl_configuration.mobile_optimize) {

            this.current_m = this.createMarker(store);
            this.current_m.setMap(_map);
            infoWindow.setPosition(store.getLocation());
            infoWindow.open(_map);
          }
          */
        else {
          infoWindow.setPosition(store.getLocation());
          infoWindow.open(_map);
        }

        //  mouseover
        if (asl_configuration.smooth_pan && !_item_clicked) {

          //_map.setZoom(parseInt(asl_configuration.zoom_li));
          //var _point = this._overlay.getProjection().fromLatLngToDivPixel(store.getLocation());
          var _point = this._overlay.getProjection().fromLatLngToContainerPixel(store.getLocation());
          _map.panBy((_point.x - asl_configuration.info_x_offset) - (this.map_w / 2), (_point.y - asl_configuration.info_y_offset) - (this.map_h / 2));
          //_map.panBy((_point.x - asl_configuration.info_x_offset)  ,(_point.y - asl_configuration.info_y_offset)  );
        } else {

          _map.setZoom(parseInt(asl_configuration.zoom_li));
          //_map.panTo(store.getLocation());
          this.map_recenter(store.getLocation(), asl_configuration.info_x_offset, asl_configuration.info_y_offset);
        }


        /*
        if (asl_configuration.load_all == '0') {
        }
        */

        //map.getStreetView().setVisible(true)
        if (_map.getStreetView().getVisible()) {
          _map.getStreetView().setPosition(store.getLocation());
        }

        // Highlight, Bring it First
        if (asl_configuration.highlight_first)
          this._panel.stores_changed(store);
      } else {
        this.getInfoWindow().close();
      }


      this.set('selectedStore', store);
    };

    asl_locator.View.prototype.selectedStore_changed = function() {
      google.maps.event.trigger(this, 'selectedStore_changed', this.get('selectedStore'));
    };

    asl_locator.ViewOptions = function() {};

    /**
     * Whether the map should update stores in the visible area when the visible
     * area changes. <code>refreshView()</code> will need to be called
     * programatically. Defaults to true.
     * @type boolean
     */
    asl_locator.ViewOptions.prototype.updateOnPan;

    /**
     * Whether the store locator should attempt to determine the user's location
     * for the initial view. Defaults to true.
     * @type boolean
     */
    asl_locator.ViewOptions.prototype.geolocation;

    /**
     * All available store features. Defaults to empty FeatureSet.
     * @type asl_locator.FeatureSet
     */
    asl_locator.ViewOptions.prototype.features;

    /**
     * The icon to use for markers representing stores.
     * @type string|google.maps.MarkerImage
     */
    asl_locator.ViewOptions.prototype.markerIcon;

    // Copyright 2012 Google Inc.

    /**
     * @author Chris Broadfoot (Google)
     * @fileoverview
     * Feature model class for Store Locator library.
     */


    /**
     * Representation of a feature of a store. (e.g. 24 hours, BYO, etc).
     * @example <pre>
     * var feature = new asl_locator.Feature('24hour', 'Open 24 Hours');
     * </pre>
     * @param {string} id unique identifier for this feature.
     * @param {string} name display name of this feature.
     * @constructor
     * @implements asl_locator_Feature
     */
    asl_locator.Feature = function(id, name, img, _s) {

      this.id_      = id;
      this.name_    = name;
      this.img_     = img;
      this.order    = _s;
    };

    asl_locator['Feature'] = asl_locator.Feature;

    /**
     * Gets this Feature's ID.
     * @return {string} this feature's ID.
     */
    asl_locator.Feature.prototype.getId = function() {
      return this.id_;
    };

    /**
     * Gets this Feature's display name.
     * @return {string} this feature's display name.
     */
    asl_locator.Feature.prototype.getDisplayName = function() {
      return this.name_;
    };

    asl_locator.Feature.prototype.toString = function() {
      return this.getDisplayName();
    };



    asl_locator.FeatureSet = function(var_args) {

      /**
       * Stores references to the actual Feature.
       * @private
       * @type {!Array.<asl_locator.Feature>}
       */
      this.array_ = [];

      /**
       * Maps from a Feature's id to its array index.
       * @private
       * @type {Object.<string, number>}
       */
      this.hash_ = {};

      for (var i = 0, feature; feature = arguments[i]; i++) {
        this.add(feature);
      }
    };

    asl_locator['FeatureSet'] = asl_locator.FeatureSet;

    /**
     * Adds the given feature to the set, if it doesn't exist in the set already.
     * Else, removes the feature from the set.
     * @param {!asl_locator.Feature} feature the feature to toggle.
     */
    asl_locator.FeatureSet.prototype.toggle = function(feature) {

      if (this.hash_[feature.id_]) {
        this.remove(feature);
      } else {

        this.add(feature);
      }

    };

    /**
     * Check if a feature exists within this set.
     * @param {!asl_locator.Feature} feature the feature.
     * @return {boolean} true if the set contains the given feature.
     */
    /*
    asl_locator.FeatureSet.prototype.contains = function(feature) {
    return feature.id_ in this.hash_;
    };
    */

    /**
     * Gets a Feature object from the set, by the feature id.
     * @param {string} featureId the feature's id.
     * @return {asl_locator.Feature} the feature, if the set contains it.
     */
    /*
    asl_locator.FeatureSet.prototype.getById = function(featureId) {

    if (featureId in this.hash_) {
      return this.array_[this.hash_[featureId]];
    }
    return null;
    };
    */

    /**
     * Adds a feature to the set.
     * @param {asl_locator.Feature} feature the feature to add.
     */
    asl_locator.FeatureSet.prototype.add = function(feature) {

      if (!feature) {
        return;
      }

      this.array_.push(feature);
      this.hash_[feature.id_] = 1;
    };

    /**
     * Removes a feature from the set, if it already exists in the set. If it does
     * not already exist in the set, this function is a no op.
     * @param {!asl_locator.Feature} feature the feature to remove.
     */
    asl_locator.FeatureSet.prototype.remove = function(feature) {

      var _feature_id = feature.id_;

      if (!this.hash_[_feature_id]) {
        return;
      }

      delete this.hash_[_feature_id];
      this.array_ = this.array_.filter(function(e) { return e && e.id_ != _feature_id });
    };

    /**
     * Get the contents of this set as an Array.
     * @return {Array.<!asl_locator.Feature>} the features in the set, in the order
     * they were inserted.
     */
    asl_locator.FeatureSet.prototype.asList = function() {

      var filtered = [];
      for (var i = 0, ii = this.array_.length; i < ii; i++) {
        var elem = this.array_[i];
        if (elem !== null) {
          filtered.push(elem);
        }
      }
      return filtered;
    };

    /**
     * Empty feature set.
     * @type asl_locator.FeatureSet
     * @const
     */
    asl_locator.FeatureSet.NONE = new asl_locator.FeatureSet;


    /**
     * An info panel, to complement the map view.
     * Provides a list of stores, location search, feature filter, and directions.
     * @example <pre>
     * var container = document.getElementById('panel');
     * var panel = new asl_locator.Panel(container, {
     *   view: view,
     *   locationSearchLabel: 'Location:'
     * });
     * google.maps.event.addListener(panel, 'geocode', function(result) {
     *   geocodeMarker.setPosition(result.geometry.location);
     * });
     * </pre>
     * @param {!Node} el the element to contain this panel.
     * @param {asl_locator.PanelOptions} opt_options
     * @constructor
     * @implements asl_locator_Panel
     */
    asl_locator.Panel = function(el, opt_options) {

      this.el_ = $(el);

      this.el_.addClass('asl_locator-panel');
      this.settings_ = $.extend({
        'locationSearch': true,
        'locationSearchLabel': 'Enter Location/ZipCode: ',
        'featureFilter': true,
        'directions': true,
        'view': null
      }, opt_options);

      this.directionsRenderer_ = new google.maps.DirectionsRenderer({
        draggable: true
      });
      this.directionsService_ = new google.maps.DirectionsService;


      //lookhere
      opt_options.view._panel = this; 

      this.init_();
    };

    asl_locator['Panel'] = asl_locator.Panel;

    asl_locator.Panel.prototype = new google.maps.MVCObject;

    /**
     * Initialise the info panel
     * @private
     */
    asl_locator.Panel.prototype.init_ = function() {

      var that = this;
      this.itemCache_ = {};

      if (this.settings_['view']) {
        this.set('view', this.settings_['view']);
      }

      this.filter_ = $('.asl-p-cont .header-search');

      // this.el_.parent().prepend(this.filter_);
      var view  = that.get('view');
      var map   = view.getMap();
      
      //When Clusters
      if (asl_configuration.cluster == '1') {

        /*asl_locator.marker_clusters = new MarkerClusterer(map, [], {
          maxZoom: parseInt(asl_configuration.cluster_max_zoom) || 9,
          gridSize: parseInt(asl_configuration.cluster_grid_size) || 90,
          styles: [{width: 30,height: 30,className: "asl-cluster-1"},{width: 40,height: 40,className: "asl-cluster-2"},{width: 50,height: 50,className: "asl-cluster-3"}],
          clusterClass: "asl-cluster",
          //imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
         imagePath: asl_configuration.URL + 'icon/m'
        });*/
        // Add a marker clusterer to manage the markers.
        asl_locator.marker_clusters = new MarkerClusterer(map, [],{
            gridSize: 50,
            imagePath: asl_configuration.URL + 'icon/m',
            maxZoom: 13,
            minimumClusterSize: 2,
           // styles: [{textSize: 14,textColor: '#000',width: 70,height: 70}],
            zoomOnClick: true
        });
      

        //  Cluste click
        /*
        google.maps.event.addListener(asl_locator.marker_clusters, 'clusterclick', function() {
            
            window.setTimeout(function() {
              
              map.setZoom(map.getZoom() - 1);
            
            }, 500);

        });
        */

      }

      if (this.settings_['locationSearch']) {

        this.locationSearch_ = this.filter_;
     
        if (typeof google.maps.places != 'undefined') {

          var _input_txt = $('.asl-p-cont #auto-complete-search,.asl-p-cont .asl-search-address')[0];
          //Add search to the stores name
          if (asl_configuration.search_type == '0') {
            this.initAutocomplete_(_input_txt);
          }

          //  Add the Geocoder for the Additional Search
          /*if(asl_configuration.additional_search) {
            this.geoCoder(_input_txt);
          }*/
        } 
        else {
          this.filter_.submit(function() {
            that.searchPosition($('input', that.locationSearch_).val());
          });
        }

        this.filter_.submit(function() {
          return false;
        });

        google.maps.event.addListener(this, 'geocode', function(place) {
          //console.log('===> site_script.js ===> GEOCODE IS INVOKED');
          if (that.searchPositionTimeout_) {
            window.clearTimeout(that.searchPositionTimeout_);
          }
          if (!place.geometry) {
            //console.log('===> site_script.js ===> SEARCHING FOR POSITION');
            that.searchPosition(place.name);
            return;
          }

          this.directionsFrom_ = place.geometry.location;

          if (that.directionsVisible_) {
            that.renderDirections_();
          }
          
          var sl = that.get('view');
          
          sl.highlight(null);
          
          var map = sl.getMap();
          
          if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
          }
          else {
            map.setCenter(place.geometry.location);
            map.setZoom(parseInt(asl_configuration.zoom_li));
          }

          sl.refreshView();

          that.listenForStoresUpdate_();
        });
      }


      //Feature Sets
      if (this.settings_['featureFilter']) {

        // TODO(cbro): update this on view_changed
        var _this = this;
        this.featureFilter_ = $('.asl-p-cont #filter-options');
        this.featureFilter_.show();

        //show the filters
        if (!asl_configuration.show_categories) {

          $('.asl-p-cont .drop_box_filter').remove();
        }


        if (asl_configuration.advance_filter)
          $('.asl-p-cont .asl-advance-filters').removeClass('hide');


        if (!asl_configuration.radius_range)
          asl_configuration.radius_range = (asl_configuration.fixed_radius) ? parseInt(asl_configuration.fixed_radius) : 1000;


        //this.featureFilter_.appendTo($('.listing-map .asl-p-cont #asl-list'));
        var allFeatures = this.get('view').getViewFeatures().asList();
        var $inner = this.featureFilter_.find('.inner-filter');

        //this.storeList_ = $('.asl-p-cont #asl-list .panel-inner');
        //this.storeList_ = this.el_.find('.panel-inner');
        this.storeList_ = this.el_.find('.sidebar-list');
       
        
      }


      this.directionsPanel_ = $('.asl-p-cont #agile-modal-direction');

      //Distance Slider
      var $direction_from = this.directionsPanel_.find('.frm-place');
      $direction_from.val('');

      if (that.dest_coords)
        that_.directionsFrom_ = that.dest_coords;

      var input_search = this.directionsPanel_.find('.frm-place')[0];
      this.input_search = new google.maps.places.Autocomplete(input_search);

      //  Apply the restriction
      var fields = ['geometry'];
      this.input_search.setFields(fields);


      var that_ = this;
      google.maps.event.addListener(this.input_search, 'place_changed',
        function() {
          that_.directionsFrom_ = this.getPlace().geometry.location;
        });

      this.directionsPanel_.find('.directions-to').attr('readonly', 'readonly');
      //this.directionsPanel_.hide();
      this.directionsVisible_ = false;

      //Direction Submit button
      this.directionsPanel_.find('.btn-submit').click(function(e) {

        if (that.dest_coords && $direction_from.val() == asl_configuration.words.current_location)
          that.directionsFrom_ = that.dest_coords || null;

        that.renderDirections_();

        return false;
      });

      if (asl_configuration.distance_unit == 'KM') {

        that.distance_type = google.maps.UnitSystem.METRIC;
        that.directionsPanel_.find('#rbtn-km')[0].checked = true;
      } 
      else
        that.distance_type = google.maps.UnitSystem.IMPERIAL;

      that.directionsPanel_.find('input[name=dist-type]').change(function() {

        that.distance_type = (this.value == 1) ? google.maps.UnitSystem.IMPERIAL : google.maps.UnitSystem.METRIC;
      });

      this.el_.find('.directions-cont .close').click(function() {
        
        that.hideDirections();
        $('.asl-p-cont .count-row').removeClass('hide');
        $('.asl-p-cont #filter-options').removeClass('hide');
      });

      this.directionsPanel_.find('.close-directions').click(function() {
        
        that.hideDirections();
        $('.asl-p-cont .count-row').removeClass('hide');
        $('.asl-p-cont #filter-options').removeClass('hide');
      });
      
         //Click tabs mobile
        $('.storelocator-navListItem button').click(function(e){
        var $mapView = $('.store-locator-maps');
        var $nav = $(".storelocator-nav");
        var $sidebar = $('.storelocator-sidebar');
        e.preventDefault();
        
        $nav.find('.active').removeClass('active');
	e.target.parentNode.classList.add('active');
        
        if (e.target.getAttribute('data-target') === 'map') {
                        $mapView.addClass('active');
                        $sidebar.removeClass('active');
			window.google.maps.event.trigger(this.map, 'resize');
		} else {
                        $sidebar.addClass('active');
			$mapView.removeClass('active');
		}
                
    
});

    };

    /**
     * Toggles a particular feature on/off in the feature filter.
     * @param {asl_locator.Feature} feature The feature to toggle.
     * @private
     */
    asl_locator.Panel.prototype.toggleFeatureFilter_ = function(feature) {

      var featureFilter = this.get('featureFilter');
      featureFilter.toggle(feature);
      this.set('featureFilter', featureFilter);
    };

    /**
     * Global Geocoder instance, for convenience.
     * @type {google.maps.Geocoder}
     * @private
     */
    asl_locator.geocoder_ = new google.maps.Geocoder;

    /**
     * Triggers an update for the store list in the Panel. Will wait for stores
     * to load asynchronously from the data source.
     * @private
     */
    asl_locator.Panel.prototype.listenForStoresUpdate_ = function() {

      var that = this;
      var view = (this.get('view'));
      if (this.storesChangedListener_) {
        google.maps.event.removeListener(this.storesChangedListener_);
      }


      ////console.log('listenForStoresUpdate_');
      this.storesChangedListener_ = google.maps.event.addListenerOnce(view,
        'stores_changed',
        function() {
          that.set('stores', view.get('stores'));
        });
    };
    /**
     * Search and pan to the specified address.
     * @param {string} searchText the address to pan to.
     */
    asl_locator.Panel.prototype.searchPosition = function(searchText) {

      var that = this;
      var request = {
        address: searchText,
        bounds: this.get('view').getMap().getBounds()
      };
      asl_locator.geocoder_.geocode(request, function(result, status) {
        if (status != google.maps.GeocoderStatus.OK) {
          //TODO(cbro): proper error handling
          return;
        }
        google.maps.event.trigger(that, 'geocode', result[0]);
      });
    };

    /**
     * Sets the associated View.
     * @param {asl_locator.View} view the view to set.
     */
    asl_locator.Panel.prototype.setView = function(view) {

      this.set('view', view);
    };

    /**
     * view_changed handler.
     * Sets up additional bindings between the info panel and the map view.
     */
    asl_locator.Panel.prototype.view_changed = function() {


      ////console.log('VIEW CHANGED...........###########');
      var that = this;
      var sl = (this.get('view'));
      this.bindTo('selectedStore', sl);

      //window['test_panel'] = that;

      if (this.geolocationListener_) {
        google.maps.event.removeListener(this.geolocationListener_);
      }

      if (this.zoomListener_) {
        google.maps.event.removeListener(this.zoomListener_);
      }

      if (this.idleListener_) {
        google.maps.event.removeListener(this.idleListener_);
      }

      var center = sl.getMap().getCenter();

      var updateList = function() {

        if (asl_instance.pan_mode) return;

        //sl.clearMarkers();
        that.listenForStoresUpdate_();
      };

      //TODO(cbro): somehow get the geolocated position and populate the 'from' box.

      this.geolocationListener_ = google.maps.event.addListener(sl, 'load', updateList);

      this.zoomListener_ = google.maps.event.addListener(sl.getMap(), 'zoom_changed', updateList);


      this.idleListener_ = google.maps.event.addListener(sl.getMap(),
        'idle',
        function() {

          return that.idle_(sl.getMap());
        });

      updateList();
      this.bindTo('featureFilter', sl);

      if (this.autoComplete_) {
        this.autoComplete_.bindTo('bounds', sl.getMap());
      }
    };


    asl_locator.Panel.prototype.geoCoder = function(_input, _callback) {

      var that = this;
      var geocoder = new google.maps.Geocoder(),
        _callback = _callback || function(results, status) {

          if (status == 'OK') {
            
            var view = that.get('view');
            
            //  Place Text Search Hook
            if(window['asl_place_search_hook'] && typeof window['asl_place_search_hook']  == 'function') {
              asl_place_search_hook.call(this, results);
            }


            //  Clear the Search Text
            if(asl_configuration.additional_search) {
              view.search_text = view._location = null;
            }

            view.measure_distance(results[0].geometry.location, true, null, results);
         
            //trigger Click of scan button
            if (asl_configuration.load_all == '2') {
              $('.asl-reload-map').trigger('click');
            }

            //  Show the close button
            $(_input).next().removeClass('hide');

          } else {
            console.log('Geocode was not successful for the following reason: ' + status);
          }
        };


      //Enter Key
      $(_input).bind('keyup', function(e) {

        if (e.keyCode == 13) {

          var addr_value = $.trim(this.value);

          if (addr_value)
            geocoder.geocode({ 'address': addr_value }, _callback);
        }
      });

      //  Pre-address, through Search Widget
      if(_input && asl_configuration['default-addr']) {

        window.setTimeout(function(){

        // req_coords
        if (asl_configuration.req_coords) {
          geocoder.geocode({ 'address': asl_configuration["default-addr"] }, _callback);
        }
        else {

          //  Geo-coder callback, trigger through search widget coordinates
          that.get('view').measure_distance(new google.maps.LatLng(asl_configuration.default_lat, asl_configuration.default_lng), true, null, null);

          //trigger Click of Scan button
          if (asl_configuration.load_all == '2') {
            $('.asl-reload-map').trigger('click');
          }
        }

        //  Show the close button
        $(_input).next().removeClass('hide');

        }, 800);
      }
      
     


      //Make it Search Button
      $('.asl-p-cont .icon-search').bind('click', function(e) {

        var addr_value = $.trim(_input.value);

        if (addr_value)
          geocoder.geocode({ 'address': addr_value }, _callback);
      });
    };

    /**
     * Adds autocomplete to the input box.
     * @private
     */
    asl_locator.Panel.prototype.initAutocomplete_ = function(input) {

      var that = this;

      if (!asl_configuration.geocoding_only) {

        var options = {};

        if (asl_configuration.google_search_type) {

          options['types'] = (asl_configuration.google_search_type == 'cities' || asl_configuration.google_search_type == 'regions') ? ['(' + asl_configuration.google_search_type + ')'] : [asl_configuration.google_search_type];
        }

        if (asl_configuration.country_restrict) {
          options['componentRestrictions'] = { country: asl_configuration.country_restrict.toLowerCase() }
        }


        this.autoComplete_ = new google.maps.places.Autocomplete(input, options);

          
        //  Limit the search scope
        var fields = ['geometry'];

        if(asl_configuration.filter_address)
          fields.push('address_components');

        this.autoComplete_.setFields(fields);


        if (this.get('view')) {
          this.autoComplete_.bindTo('bounds', this.get('view').getMap());
        }


        google.maps.event.addListener(this.autoComplete_, 'place_changed',
          function() {
            var p = this.getPlace();


            if (asl_configuration.analytics) {

              
              //  Add the address when API doesn't provide it
              if(!p.formatted_address) {
                p.formatted_address = input.value;
              }

              //Send a request and save the log
              asl_locator.save_analytics(p);
            }

            //lucia
            if (p.geometry) {

              that.get('view').measure_distance(p.geometry.location, true, null, p);

              if(window['asl_place_search_hook'] && typeof window['asl_place_search_hook']  == 'function') {
                asl_place_search_hook.call(this, p);
              }

              //  show the cross button
              $(input).next().removeClass('hide');

              //trigger Click of Scan button
              if (asl_configuration.load_all == '2') {

                $('.asl-reload-map').trigger('click');
              }
            }


            //trigger geocode
            //google.maps.event.trigger(that, 'geocode', p);
          });

      }

      //  Entery Key
      that.geoCoder(input);
    };

    /**
     * Called on the view's map idle event. Refreshes the store list if the
     * user has navigated far away enough.
     * @param {google.maps.Map} map the current view's map.
     * @private
     */
    asl_locator.Panel.prototype.idle_ = function(map) {

      if (!this.center_) {
        this.center_ = map.getCenter();
      } else if (!map.getBounds().contains(this.center_)) {
        this.center_ = map.getCenter();
        this.listenForStoresUpdate_();
      }
    };




    /**
     * Handler for stores_changed. Updates the list of stores.
     * @this asl_locator.Panel
     */
    asl_locator.Panel.prototype.stores_changed = function(_store) {
        
        
        
           //categoria load
 // $('input[name=category]').change(function(){
$('input[name=category]').bind('click', function(e) {

if($('input[name=category]').is(':checked')){
  /* var sl = $(this).get('view');
    // $(this).get('view').refreshView(), asl_configuration.category_bound && $(this).get("view").fit_bound(null);
        asl_configuration.category_bound && $(this).get("view").fit_bound(null);*/
         
           var li_cat_id = $(this).data('category-id');

          if(li_cat_id) {
            //  Get all the Stores with the Category ID li_cat_id
            var stores = view.storesWithCategory(String(li_cat_id));
            view.fitBound(stores);
             view.refreshView(), asl_configuration.category_bound && view.fitBound(null);

     console.log(li_cat_id);

     /*   alert('Checked');
    } else {
        alert('Not checked');*/
    }
}
});

      if (!this.get('stores')) {
        return;
      }

      var that = this;
      var view = this.get('view');

      if (view.showing_direction) return;

      ///////////////////////////
      //Once for the Accordion //
      ///////////////////////////
      if (asl_configuration.accordion) {
        if (view.is_updated) return;
      }

      view.is_updated = true;


      var bounds = view && view.getMap().getBounds();
      //var stores = this.get('stores');
      var stores = view.get('stores');
      var selectedStore = this.get('selectedStore');


      //var stores = this.get('stores');
      //var stores = asl_panel.get('view').get('stores');

      //  Highlight the Clicked Element, Make it first
      if (asl_configuration.highlight_first && _store) {

        var high_arr = [],
          low_arr = [];
        for (var s = 0; s < stores.length; s++) {

          if (stores[s].id_ == _store.id_)
            high_arr.push(stores[s]);
          else
            low_arr.push(stores[s]);
        }

        stores = high_arr.concat(low_arr);
      }

      if (asl_configuration.highlight_first && _store) {

        jQuery('.asl-p-cont #asl-list').animate({ scrollTop: (0) }, 'fast');
      }


      if (!asl_configuration.accordion)
        this.storeList_.empty();

      if (!stores.length) {

        $('.asl-p-cont .Num_of_store .count-result').html('0');
        that.storeList_.html('<div class="asl-overlay-on-item" id="asl-no-item-found"><div class="white"></div><h2 class="h2 text-center pt-4">' + asl_configuration.no_item_text + '</h2></div>');
      } else if (bounds && !bounds.contains(stores[0].getLocation())) {

        $('.asl-p-cont .Num_of_store .count-result').html(stores.length);
      } else {
        $('.asl-p-cont .Num_of_store .count-result').html(stores.length);
      }


      var clickHandler = function(e) {

        var $e = $(e.target);

        if ($e.hasClass('s-direction')) {
          e.preventDefault();
          return;
        }
        
        if (e.target.className == 'A' || asl_configuration.disable_list_click) {
          return;
        }

        view.noRefreshList = true;
        view.highlight(this['store'], true);

        //  Scroll to the Map if Mobile
        if (asl_configuration.is_mob && window.innerWidth <= 480) {

          $('html, body')
            .stop()
            .animate({
              'scrollTop': $(view.getMap().getDiv()).offset().top
            }, 900, 'swing');

        }
      };




      // TODO(cbro): change 10 to a setting/option
      //add the stores and the click event    
      for (var i = 0, ii = stores.length; i < ii; i++) {


        var storeLi = stores[i].getInfoPanelItem();
        storeLi['store'] = stores[i];
        var store_inst = stores[i];


        if (selectedStore && stores[i].id_ == selectedStore.id_) {
          $(storeLi).addClass('highlighted');
        }

        if (!storeLi.clickHandler_) {
          storeLi.clickHandler_ = google.maps.event.addDomListener(storeLi, 'click', clickHandler);
        }


        storeLi.addEventListener('mouseenter', view.doBounce.bind(view, stores[i], true));
        storeLi.addEventListener('mouseleave', view.doBounce.bind(view, stores[i], false));

        //The direction link on panel
        //direction Click Handler
        $(storeLi).find('.s-direction').click(function(e) {
            var store = $(this).data('_store');
            that.directionsTo_ = store;
            that.showDirections(store);


          })
          .data('_store', stores[i]);

        that.storeList_.append(storeLi);
      }


      /// Open the Category Accordion Markers
      if(asl_configuration.category_accordion) {

        //  Bind Event for the Category Li-State Click
        $('.asl-p-cont li.item-state').bind('click', function(e) {

          var li_cat_id = $(this).data('id'),
              li_state  = $(this).children(0).hasClass('colisiond');

          if(li_cat_id && li_state) {
              
            //  Get all the Stores with the Category ID li_cat_id
            var stores = view.storesWithCategory(String(li_cat_id));
            view.fitBound(stores);

          }
        });
      }


      //  Open the first Store Item
      /*if(asl_configuration.accordion) {

        $('.asl-p-cont li.item-state').on('shown.bs.colision', function (e) {

          if($(this).hasClass('asl-country-li')) {

            if($(this).find('.colision')[1]) {
              jQuery($(this).find('.colision')[1]).colision('show')
            }

            var a_store = $(this).find('.item').first();
            if(a_store && a_store[0]) {

              a_store.trigger('click');
            }
          }
        })
      }*/

      //jQuery('.thumb-a img').each(function(e){ jQuery(this).attr('src', jQuery(this).attr('data-src'))  });


      //  New code to remove empty ul
      $('.asl-p-cont .item-state ul:empty').each(function(e) {
        $(this).parent().parent().remove();
      });
    };

    /**
     * Handler for selectedStore_changed. Highlights the selected store in the
     * store list.
     * @this asl_locator.Panel
     */
    asl_locator.Panel.prototype.selectedStore_changed = function() {


      $('.highlighted', this.storeList_).removeClass('highlighted');

      var that = this;
      var store = this.get('selectedStore');
      var view = that.get('view');

      //  Remove the active_marker active state
      if (view.active_marker && view.active_marker.m) {

        view.active_marker.m.setIcon(view.active_marker.picon);
        view.active_marker.m = null;
      }

      if (!store) {
        return;
      }


      // majin icon change here
      var store_marker = store.getMarker();

      // Backup Store marker
      if (view.active_marker) {

        view.active_marker.picon = store_marker.getIcon();
        view.active_marker.m = store_marker;
        store_marker.setIcon(view.active_marker.icon);
      }


      this.directionsTo_ = store;
      var store_li = this.storeList_.find('div[data-id="' + store.id_ + '"]');
      
       var $sidebar = $('.storelocator-sidebar');
       var $mapView = $('.store-locator-maps');
                      	

      if (store_li) {

        store_li.addClass('highlighted');
        $mapView.addClass('active');
        $sidebar.removeClass('active');
       // console.log($mapView);
	window.google.maps.event.trigger(this.map, 'resize');
         
         
      }

      if (this.settings_['directions']) {
        this.directionsPanel_.find('.directions-to')
          .val(store.getDetails().title);
      }

      var node = that.get('view').getInfoWindow().getContent();
      var directionsLink = $('<a/>').text(asl_configuration.words.direction).attr('href', 'javascript:void(0)').addClass('action').addClass('directions');

      // TODO(cbro): Make these two permanent fixtures in InfoWindow.
      // Move out of Panel.
      var zoomLink = $('<a/>')
        .text(asl_configuration.words.zoom)
        .attr('href', 'javascript:void(0)')
        .addClass('action')
        .addClass('zoomhere');


      var link = store.props_['website'];


      directionsLink.click(function() {

        that.showDirections();
        return false;
      });

      zoomLink.click(function() {

        that.get('view').getMap().setOptions({
          center: store.getLocation(),
          zoom: (asl_map.getZoom() + 1)
        });
      });


      $(node).find('.asl-buttons')
        .append(directionsLink)
        .append(zoomLink);


      var streetViewLink = $('<a/>').text(asl_configuration.words.detail).addClass('action').addClass('a-website');
      
      //  If have an event hook for it
      if(window['asl_website_click']) {
        streetViewLink.click(function(){
          asl_website_click(store.props_, link);
        });

        $(node).find('.asl-buttons').append(streetViewLink);
      }
      //   If have a link for it
      else if (link) {

        $(node).find('.asl-buttons').append(streetViewLink);

        streetViewLink.attr('href', link);

        if (asl_configuration.target_blank == '1')
          streetViewLink.attr('target', '_blank');
      }

    };

    /**
     * Hides the directions panel.
     */
    asl_locator.Panel.prototype.hideDirections = function() {

      this.directionsVisible_ = false;
      this.directionsPanel_.removeClass('in');
      this.el_.find('.directions-cont').addClass('hide');
      //this.featureFilter_.fadeIn();
      this.storeList_.fadeIn();
      this.directionsRenderer_.setMap(null);

      var view = this.get('view');
      view.showing_direction = false;
    };

    /**
     * Shows directions to the selected store.
     */
    asl_locator.Panel.prototype.showDirections = function(_store) {

      var store = this.get('selectedStore') || _store;
      if (!store) return;

      //if mobile device redirect to location
      //_isMobileDevice()
      if ((_isMobileDevice() && asl_configuration.direction_redirect == '1') || asl_configuration.direction_redirect == '2') {

        //var destin = store.location_.lat() + ',' + store.location_.lng(),
        // source = '';

        var destin = (asl_configuration.title_in_dir)? [store.props_.title]:[],
            source = '';

        destin.push(store.props_.address.replace(/<\/?[^>]+(>|$)/g," "));
        destin = destin.join(', ');

        //  Direction with the Coordinates
        if(asl_configuration.coords_direction) {
          destin = store.location_.lat() + ',' + store.location_.lng();
        }

        //check if source available
        /*
        if(this.dest_coords) {
          source = this.dest_coords.lat() +','+this.dest_coords.lng();
        }
        else
          source = 'Current+Location';
        */

        //https://developers.google.com/maps/documentation/urls/ios-urlscheme
        var _path = '';
        
        if ((navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPad") != -1) || (navigator.platform.indexOf("iPod") != -1))
          //_path = "maps://maps.google.com/maps?daddr="+destin+"&amp;ll=";
          _path = "https://maps.google.com/maps?daddr=" + destin + "&amp;ll=";
        else 
          if(asl_configuration.fix_direction)
          _path = "https://www.google.com/maps/dir/" + asl_configuration.words.cur_dir + "/" + destin + "?hl=" + asl_configuration.map_language + "";
        else
        //  _path = "https://www.google.com/maps/dir/?api=1&destination=" + destin;

        _path   = "https://www.google.com/maps/dir/Current+Location/"+destin+"?hl=" + asl_configuration.map_language +"";
        window.open(_path, '_blank');
        return true;
        console.log(_path);
      }

      this.directionsPanel_.find('.frm-place').val((this.dest_coords) ? asl_configuration.words.current_location : '');

      //this.featureFilter_.fadeOut();
      this.directionsPanel_.find('.directions-to').val(store.getDetails().title);
      this.directionsPanel_.addClass('in');
      this.renderDirections_();

      this.directionsVisible_ = true;
    };

    /**
     * Renders directions from the location in the input box, to the store that is
     * pre-filled in the 'to' box.
     * @private
     */
    asl_locator.Panel.prototype.renderDirections_ = function() {

      var that = this;

      if (!this.directionsFrom_ || !this.directionsTo_) {
        return;
      }


      this.el_.find('#map-loading').show();

      this.el_.find('.directions-cont').removeClass('hide');
      this.storeList_.fadeOut();
      that.directionsPanel_.removeClass('in');
      var rendered = this.el_.find('.rendered-directions').empty();


      this.directionsService_.route({
          origin: this.directionsFrom_,
          destination: this.directionsTo_.getLocation(),
          travelMode: google.maps['DirectionsTravelMode'].DRIVING,
          unitSystem: that.distance_type
          //TODO(cbro): region biasing, waypoints, travelmode
        },
        function(result, status) {
          that.el_.find('#map-loading').hide();
          if (status != google.maps.DirectionsStatus.OK) {
            // TODO(cbro): better error handling
            return;
          }

          $('.asl-p-cont .count-row').addClass('hide');
          $('.asl-p-cont #filter-options').addClass('hide');

          var view = that.get('view');
          view.showing_direction = true;

          var renderer = that.directionsRenderer_;
          renderer.setPanel(rendered[0]);
          renderer.setMap(that.get('view').getMap());
          renderer.setDirections(result);
        });

      //make it null again
      this.directionsFrom_ = null;
    };

    /**
     * featureFilter_changed event handler.
     */
    asl_locator.Panel.prototype.featureFilter_changed = function() {

      this.listenForStoresUpdate_();
    };



    /**
     * @example see asl_locator.Panel
     * @interface
     */
    asl_locator.PanelOptions = function() {};

    /**
     * Whether to show the location search box. Default is true.
     * @type boolean
     */
    asl_locator.prototype.locationSearch;

    /**
     * The label to show above the location search box. Default is "Where are you
     * now?".
     * @type string
     */
    asl_locator.PanelOptions.prototype.locationSearchLabel;

    /**
     * Whether to show the feature filter picker. Default is true.
     * @type boolean
     */
    asl_locator.PanelOptions.prototype.featureFilter;

    /**
     * Whether to provide directions. Deafult is true.
     * @type boolean
     */
    asl_locator.PanelOptions.prototype.directions;

    /**
     * The store locator model to bind to.
     * @type asl_locator.View
     */
    asl_locator.PanelOptions.prototype.view;
  })(jQuery);


  /*Site Script*/
  (function($, _) {

    //Main map instance
    var map = null;

    var asl_engine = {
      config: {},
      helper: {}
    };
    window['asl_engine'] = asl_engine;



    if (!window['asl_configuration']) return;
    asl_configuration.category_accordion = (asl_configuration.layout == '2')? true: false;
    asl_configuration.accordion = (asl_configuration.layout == '1' || asl_configuration.category_accordion) ? true : false;
    asl_configuration.analytics = (asl_configuration.analytics == '1') ? true : false;
    asl_configuration.sort_by_bound = (asl_configuration.sort_by_bound == '1') ? true : false;
    asl_configuration.scroll_wheel = (asl_configuration.scroll_wheel == '1') ? true : false;
    asl_configuration.distance_slider = (asl_configuration.distance_slider == '1') ? true : false;
    asl_configuration.show_categories = (asl_configuration.show_categories == '0') ? false : true;
    asl_configuration.time_switch = (asl_configuration.time_switch == '0') ? false : true;
    asl_configuration.category_marker = false;
    asl_configuration.load_all = '1';
    asl_configuration.advance_filter = (asl_configuration.advance_filter == '0') ? false : true;
    asl_configuration.time_24 = (asl_configuration.time_format == '1') ? true : false;
    asl_configuration.smooth_pan = (asl_configuration.smooth_pan == '1') ? true : false;
    asl_configuration.week_hours = (asl_configuration.week_hours == '1') ? true : false;
    asl_configuration.user_center = false;
    asl_configuration.distance_unit  = (asl_configuration.distance_unit == 'KM') ? asl_configuration.distance_unit : 'Miles';
    asl_configuration.filter_address = false;
    asl_configuration.regex = (asl_configuration.no_regex) ? /#|\./ig : /[^a-z0-9\s]/gi;
    asl_configuration.info_x_offset = (asl_configuration.info_x_offset && !isNaN(asl_configuration.info_x_offset)) ? parseInt(asl_configuration.info_x_offset) : 0;
    asl_configuration.info_y_offset = (asl_configuration.info_y_offset && !isNaN(asl_configuration.info_y_offset)) ? parseInt(asl_configuration.info_y_offset) : 0;
    asl_configuration.enter_key = true;
    asl_configuration.category_sort = true;
    asl_configuration.stores_limit = (asl_configuration.stores_limit && !isNaN(asl_configuration.stores_limit)) ? parseInt(asl_configuration.stores_limit) : null;
    asl_configuration.radius_circle       = false;
    asl_configuration.marker_height = (asl_configuration.marker_height)? asl_configuration.marker_height: '43';
    asl_configuration.and_filter    = (asl_configuration.and_filter == '1') ? true : false;
    asl_configuration.category_bound= (asl_configuration.category_bound == '1') ? true : false;
    asl_configuration.fit_bound     = (asl_configuration.fit_bound == '1') ? true : false;
    asl_configuration.sort_random   = (asl_configuration.sort_random == '1') ? true : false;

    asl_configuration.filter_ddl = null;

    

    //  Add the offset
    if(!asl_configuration.info_y_offset) {

      //  Default
      asl_configuration.info_y_offset = -30;

      if(asl_configuration.infobox_layout == '1')
        asl_configuration.info_y_offset = -150;
    }

    // Error with it
    asl_configuration.smooth_pan    = false;


    //API Key error
    window['gm_authFailure'] = function() {

      var $err_msg = $('<div class="alert alert-danger asl-geo-err"></div>');
      $err_msg.html("This API project is not authorized to use this API. Please ensure this API is activated in the Google Developers.");

      $err_msg.appendTo('.asl-p-cont');

      window.setTimeout(function() {
        $err_msg.remove();
      }, 5000);
    };

    //Remove Extra P Tags
    if (asl_configuration.no_script) {
      $('.asl-p-cont p:empty').remove();
    }

    asl_configuration.fixed_radius = (asl_configuration.fixed_radius && !isNaN(asl_configuration.fixed_radius)) ? parseInt(asl_configuration.fixed_radius) : null;

    //asl_configuration.mobile_optimize = true;
    asl_configuration.is_mob = _isMobileDevice();
    //asl_configuration.mobile_optimize = (asl_configuration.is_mob && asl_configuration.mobile_optimize == '1')?true:false;


    //  Geocoding Only
    if (asl_configuration.search_type == '3') {

      asl_configuration.search_type = '0';
      asl_configuration.geocoding_only = true;
    }

    //Fixed
    if (asl_configuration.full_height != '') {
      $('#asl-storelocator').css('height', jQuery(window).height() + 'px');
    }

    //Only for load all
    /*if(asl_configuration.load_all != "1")
      asl_configuration.search_type = '0';*/

    //Set Mobile Load on Bound
    if (asl_configuration.is_mob && asl_configuration.mobile_load_bound) {

      asl_configuration.load_all = '2';
      asl_configuration.search_type = '0';
    }




    //Set Mobile Stores Limit
    if (asl_configuration.is_mob && asl_configuration.mobile_stores_limit) {

      asl_configuration.stores_limit = (asl_configuration.mobile_stores_limit && !isNaN(asl_configuration.mobile_stores_limit)) ? parseInt(asl_configuration.mobile_stores_limit) : null;
    }

    if (!asl_configuration.advance_filter)
      $('.asl-p-cont').addClass('no-asl-filters');

    //Accordion only work with Load Once
    if (asl_configuration.accordion) {
      asl_configuration.load_all = "1";
      asl_configuration.filter_address = asl_configuration.advance_filter = false;
    }
    else
        asl_configuration.mobile_stores_limit = (asl_configuration.mobile_stores_limit) ? parseInt(asl_configuration.mobile_stores_limit) : 100;


 

    /*ALL HELPER METHODS*/
    var asl_lat = (asl_configuration.default_lat) ? parseFloat(asl_configuration.default_lat) : 39.9217698526,
      asl_lng = (asl_configuration.default_lng) ? parseFloat(asl_configuration.default_lng) : -75.5718432,
      categories = {},
      asl_date = new Date();

    asl_configuration.default_lat = asl_lat;
    asl_configuration.default_lng = asl_lng;


    //HARDCODE MODE
    /*
    asl_configuration.show_categories = true; 
    asl_configuration.advance_filter = true;  
    asl_configuration.additional_info = true;
    */
    /*
    asl_configuration.range_slider = true;
    asl_configuration.show_distance = true; 
    asl_configuration.direction = true; 
    asl_configuration.additional_info = true; 
    asl_configuration.distance_unit = true; 
    asl_configuration.cluster = true; 
    */
    asl_configuration.show_opened = false;

    $('#asl-dist-unit').html(asl_configuration.distance_unit);


    /**
     * [merge Merge two array]
     * @param  {[type]} a [description]
     * @param  {[type]} b [description]
     * @return {[type]}   [description]
     */
    asl_engine.helper.merge = function(a, b) {

      var hash = {};
      var i;
      
      for (i = 0; i < a.length; i++) {hash[a[i]] = true;}
      for (i = 0; i < b.length; i++) {hash[b[i]] = true;}
      
      return Object.keys(hash);
    };

    /**
     * [asl_leadzero description]
     * @param  {[type]} n [description]
     * @return {[type]}   [description]
     */
    asl_engine.helper.asl_leadzero = function(n) {
      return n > 9 ? "" + n : "0" + n;
    };

    /**
     * [asl_timeConvert description]
     * @param  {[type]} _str [description]
     * @return {[type]}      [description]
     */
    asl_engine.helper.asl_timeConvert = function(_str) {

      if(!_str)return 0;

      var time  = $.trim(_str).toUpperCase();

      if(/(1[012]|[0-9]):[0-5][0-9]$/.test(time)) {

      var hours   = Number(time.match(/^(\d+)/)[1]);
      var minutes = Number(time.match(/:(\d+)/)[1]);

      return hours+(minutes / 100);
      }
      else if(/(1[012]|[1-9]):[0-5][0-9][ ]?(AM|PM)/.test(time)) {
        
        var hours   = Number(time.match(/^(\d+)/)[1]);
        var minutes = Number(time.match(/:(\d+)/)[1]);
        var AMPM    = (time.indexOf('PM') != -1)?'PM':'AM';

        if (AMPM == "PM" && hours < 12) hours  = hours + 12;
      if (AMPM == "AM" && hours == 12) hours = hours - 12;

        return hours+(minutes / 100);
      }
      else
        return 0;

    };

    /**
     * [between description]
     * @param  {[type]} number [description]
     * @param  {[type]} lower  [description]
     * @param  {[type]} upper  [description]
     * @return {[type]}        [description]
     */
    asl_engine.helper.between = function(number, lower, upper) {
      return number > lower && number < upper;
    };

    /**
     * [implode description]
     * @param  {[type]} arr [description]
     * @param  {[type]} sep [description]
     * @return {[type]}     [description]
     */
    asl_engine.helper.implode = function(arr, sep) {
      var parts = [];
      for (var i = 0, ii = arr.length; i < ii; i++) {
        arr[i] && parts.push(arr[i]);
      }
      return parts.join(sep);
    };

    /**
     * [toObject_ description]
     * @param  {[type]} headings [description]
     * @param  {[type]} row      [description]
     * @return {[type]}          [description]
     */
    asl_engine.helper.toObject_ = function(headings, row) {

      var result = {};
      for (var i = 0, ii = row.length; i < ii; i++) {
        result[headings[i]] = row[i];
      }
      return result;
    };


    /**
     * [distanceCalc description]
     * @param  {[type]} point [description]
     * @return {[type]}       [description]
     */
    asl_engine.helper.distanceCalc = function(point) {

      var R = 6371; // mean radius of earth
      var location = this.getLocation();
      var lat1 = asl_locator.toRad_(location.lat());
      var lon1 = asl_locator.toRad_(location.lng());
      var lat2 = asl_locator.toRad_(point.lat());
      var lon2 = asl_locator.toRad_(point.lng());
      var dLat = lat2 - lat1;
      var dLon = lon2 - lon1;

      var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1) * Math.cos(lat2) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      return R * c;
    };


    /**
     * [multi_sort description]
     * @param  {[type]} _items   [description]
     * @param  {[type]} _param_1 [description]
     * @param  {[type]} _param_2 [description]
     * @return {[type]}          [description]
     */
    asl_engine.helper.multi_sort =  function(_items, _param_1, _param_2) {

      _items.sort(function(a, b) {

        var aVal = a[_param_1];
        var bVal = b[_param_1];
        var aNum = a[_param_2];
        var bNum = b[_param_2];

        if (aVal == bVal) {

          return (aNum < bNum) ? -1 : (aNum > bNum) ? 1 : 0;
        } else {
          return (aVal < bVal) ? -1 : 1;
        }
      });
    };
    /*HELPER ENDS HERE*/


    /*All the DATAFEED*/
    asl_engine.dataSource = function() {

      this.stores_ = [];
      this.remote_url = ASL_REMOTE.ajax_url;
    };

    /**
     * [sortDistance description]
     * @param  {[type]} latLng [description]
     * @param  {[type]} stores [description]
     * @return {[type]}        [description]
     */
    asl_engine.dataSource.prototype.sortDistance = function(latLng, stores) {

      stores = stores.sort(function(a, b) {
        return a.distanceTo(latLng) - b.distanceTo(latLng);
      });
    };


    /**
     * [sortRandom Sort by random]
     * @param  {[type]} stores [description]
     * @return {[type]}        [description]
     */
    asl_engine.dataSource.prototype.sortRandom  = function(stores) {

        stores = stores.sort(function(a, b) {
          return Math.random() - 0.5;
        });
    };

    /**
     * [sortBy Sort By Function]
     * @param  {[type]} _type  [description]
     * @param  {[type]} stores [description]
     * @return {[type]}        [description]
     */
    asl_engine.dataSource.prototype.sortBy = function(_type, stores) {

      if (_type == 'cat') {
        
        stores.sort(function(a, b) {

          //return (a.props_[_type] > b.props_[_type]) ? 1 : ((b.props_[_type] > a.props_[_type]) ? -1 : 0);

          var aVal = a.props_['cat'];
          var bVal = b.props_['cat'];
          var aDist = a.props_['distance'];
          var bDist = b.props_['distance'];

          if (aVal == bVal) {

            return (aDist < bDist) ? -1 : (aDist > bDist) ? 1 : 0;
          } else {
            return (aVal < bVal) ? -1 : 1;
          }
        });
      }
      else
        stores.sort(function(a, b) {
          return (a.props_[_type] > b.props_[_type]) ? 1 : ((b.props_[_type] > a.props_[_type]) ? -1 : 0);
        });
    };


    /**
     * [sortByDesc Sort by Desc]
     * @param  {[type]} _type  [description]
     * @param  {[type]} stores [description]
     * @return {[type]}        [description]
     */
    asl_engine.dataSource.prototype.sortByDesc = function(_type, stores) {

      stores.sort(function(a, b) {

        return (a.props_[_type] < b.props_[_type]) ? 1 : ((b.props_[_type] < a.props_[_type]) ? -1 : 0);
      });
    };


    var not_initial_load = false,
      asl_view = null,
      asl_panel = null;


    /**
     * [fetch_remote_data Fetch the remote Data]
     * @param  {[type]} _position [description]
     * @return {[type]}           [description]
     */
    asl_engine.dataSource.prototype.fetch_remote_data = function(_position) {

      var that = this;

      $('.asl-p-cont .asl-overlay').show();


      //stop multiple request
      //if(that.is_loading)return;

      var params = {
        action: 'asl_load_stores',
        nonce: ASL_REMOTE.nonce,
        load_all: asl_configuration.load_all,
        layout: (asl_configuration.layout) ? 1 : 0
      };

      //limited markers
      if (asl_configuration.stores) {
        params['stores'] = asl_configuration.stores;
      }

      //Not Load all
      if (asl_configuration.load_all != '1') {

        var bounds = map.getBounds(),
          ne = bounds.getNorthEast(), // LatLng of the north-east corner
          sw = bounds.getSouthWest(), // LatLng of the south-west corder
          center = bounds.getCenter();

        params['lat'] = center.lat(),
          params['lng'] = center.lng(),
          params['nw'] = [ne.lat(), sw.lng()],
          params['se'] = [sw.lat(), ne.lng()];
      }

      if (asl_configuration.category) {

        params['category'] = asl_configuration.category;
      }
      

      /*
      if(that.on_call)
        return;
      */

      that.on_call = true;

      $.ajax({
        url: ASL_REMOTE.ajax_url,
        data: params,
        type: 'GET',
        dataType: 'json',
        success: function(_data) {

          that.stores_ = that.parseData(_data);

          //that.is_loading = false;

          //remove spinner
          if (asl_configuration.load_all == '2') {

            $('.asl-reload-map').find('i').removeClass('animate-spin');
          }
          



          //  Load Once
          if (!not_initial_load) {

            var _input_txt = $('.asl-p-cont #auto-complete-search,.asl-p-cont .asl-search-address');

            _input_txt.after(ASL_CLOSE_BUTTON);

            _input_txt.next().bind('click', function(e) {

              asl_view.search_text = asl_view._location = null;
              asl_view.reset_measure(_input_txt);
              asl_view._panel.stores_changed();
              $(this).addClass('hide');
            
              //  Reset Map to same location where it was initially
              asl_view.getMap().panTo(new google.maps.LatLng(asl_configuration.default_lat, asl_configuration.default_lng));
              asl_view.getMap().setZoom(parseInt(asl_configuration.zoom));

            });
          }
          

          
          //  Add search location end
          var all_stores = that.stores_;

          var country = (all_stores[0]) ? all_stores[0].props_.country : null;
          that.countries = false;


          for (var j = 0; j < all_stores.length; j++) {


            if (country != all_stores[j].props_.country) {
              that.countries = true;
              break;
            }
          }


          // Only Once loaded
          if (!not_initial_load) {

            not_initial_load = true;

            //that.load_locator(all_stores[0],that.countries);
            asl_view = new asl_locator.View(map, that, {
              geolocation: false,
              features: that.getDSFeatures()
            });


            asl_panel = new asl_locator.Panel(document.getElementById('asl-list'), {
              view: asl_view
            });

            //  Lookhere
            //asl_view._panel = asl_panel;


            asl_view.load_complete();

            window['asl_view'] = asl_view;
            
           
            if (asl_configuration.smooth_pan) {

              //the dummy overlay
              asl_view._overlay = new google.maps.OverlayView();
              asl_view._overlay.draw = function() {};
              asl_view._overlay.setMap(map);


              //capture map details
              function calc_dims() {

                asl_view.map_w = $(map.getDiv()).width();
                asl_view.map_h = $(map.getDiv()).height();
              };

              $(window).resize(calc_dims);
              calc_dims();
            }


            var $agile_modal = jQuery('.asl-p-cont #asl-geolocation-agile-modal');


            //  Close Dailog Event
            $agile_modal.find('.close').bind('click', function(e) {

              $agile_modal.removeClass('in');
              window.setTimeout(function() {
                $agile_modal.css('display', 'none');
              }, 300);
            });

             /**
             * [_promptLocResRender Will remove the dailog and execute measure_distance]
             * @param  {[type]} geo_place [description]
             * @return {[type]}           [description]
             */
            function _promptLocResRender(geo_place) {

              asl_view.measure_distance(geo_place.geometry.location, true, null, geo_place);

              $agile_modal.removeClass('in').css('display', 'none');

              if (asl_configuration.analytics) {
                //asl_locator.save_analytics(p);
              }
            };


            //  Auto Without Prompt
            if (asl_configuration.prompt_location == '3') {
              asl_view.geolocate_();
            }
            //  Prompt using the Dailog
            else if (asl_configuration.prompt_location != '0') {

              $agile_modal.css('display', 'block');

              window.setTimeout(function() {

                $agile_modal.addClass('in');

              }, 300);

              //IF PROMPT is ON
              $('.asl-p-cont #asl-btn-geolocation').bind('click', function() {
               asl_view.geolocate_();
             
                $agile_modal.removeClass('in').css('display', 'none');
              });
            }

            // For GEO Locate Prompt Dailog
            if (asl_configuration.prompt_location == "2") {

              var geo_place = null;

              var geocoder = new google.maps.Geocoder(),
                _callback = _callback || function(results, status) {

                  if (status == 'OK') {

                    _promptLocResRender(results[0]);

                  } else {
                    console.log('Geocode was not successful for the following reason: ' + status);
                  }
                };


              // Enter Key
              $('#asl-current-loc').bind('keyup', function(e) {

                if (e.keyCode == 13) {

                  var addr_value = $.trim(this.value);

                  if (addr_value)
                    geocoder.geocode({ 'address': addr_value }, _callback);
                }
              });

              //Event of geolocate 
              $('#asl-btn-locate').click(function(e) {


                // When we have it through Place API
                if (geo_place) {
                  _promptLocResRender(geo_place);
                  return;
                }


                // For simple button click when no data
                var addr_value = $.trim($('#asl-current-loc').val());
                

                  if (addr_value)
                    geocoder.geocode({ 'address': addr_value }, _callback);
              });

              var input = $('.asl-p-cont #asl-current-loc')[0];

              var options = {};

              if (asl_configuration.google_search_type) {
                options['types'] = ['(' + asl_configuration.google_search_type + ')'];
              }

              if (asl_configuration.country_restrict) {
                options['componentRestrictions'] = { country: asl_configuration.country_restrict.toLowerCase() }
              }


              var autoComplete_ = new google.maps.places.Autocomplete(input, options);

              //  Limit the search scope
              var fields = ['geometry'];

              if(asl_configuration.filter_address)
                fields.push('address_components');

              autoComplete_.setFields(fields);


              //  Simple geocoder with enter key
              /*if (asl_configuration.enter_key)
              asl_panel.geoCoder(input, function(results, status) {

                if (status == 'OK') {

                  //results[0].geometry.location;
                  geo_place = results[0];
                }
              });*/

              google.maps.event.addListener(autoComplete_, 'place_changed',

                function() {
                  var p = this.getPlace();

                  geo_place = p;
                });
            }

            //GEO Locate Current Location
            $('.asl-p-cont .asl-geo.icon-direction-outline').bind('click', function(e) {
              asl_view.geolocate_();
            });




            //for users who want fixed lat/lng for home location

            if (asl_configuration.user_center)
              asl_view.measure_distance(new google.maps.LatLng(asl_configuration.default_lat, asl_configuration.default_lng));

          }

          //view.geolocate_();
         

          //recalculate distance for ajax
          if (asl_configuration.load_all != '1' && asl_view.dest_coords) {
            asl_view.measure_distance(asl_view.dest_coords, null, true);
          }


          //first callee
          if (!asl_configuration.first_load || asl_configuration.first_load == '1')
            asl_view.refreshView(true);

          $('.asl-p-cont .asl-overlay').hide();

          //Center to the Pos
          /*
          if(that.pos)
            map.panTo(that.pos);
          */
          if (_position) {
            map.panTo(_position);
          }

          that.on_call = false;

        },
        error: function() {
          that.on_call = false;
        },
        dataType: 'json'
      });


      /*Save that pos*/
      that.pos = center;
    };

    /**
     * [load_locator Load the Locator]
     * @return {[type]} [description]
     */
    asl_engine.dataSource.prototype.load_locator = function() { /* _store,_morecountries */

      var that = this;

      //Only if found
      if (!document.getElementById('asl-map-canv'))
        return false;


      var maps_params = {
        center: new google.maps.LatLng(asl_lat, asl_lng),
       // center: new google.maps.LatLng(46.227638, 2.213749),
        zoom: parseInt(asl_configuration.zoom),
        scrollwheel: asl_configuration.scroll_wheel,
        gestureHandling: asl_configuration.gesture_handling || 'cooperative', //cooperative,greedy
        mapTypeId: asl_configuration.map_type,
        disableDefaultUI: false,
        fullscreenControl: true,
        mapTypeControl: false,
        scaleControl: false,
        scrollwheel: true,
        streetViewControl: false
      };

      if (asl_configuration.zoomcontrol == 'false') maps_params.zoomControl = false;
      if (asl_configuration.maptypecontrol == 'false') maps_params.mapTypeControl = false;
      if (asl_configuration.scalecontrol == 'false') maps_params.scaleControl = false;
      if (asl_configuration.rotatecontrol == 'false') maps_params.rotateControl = false;
      if (asl_configuration.fullscreencontrol == 'false') maps_params.fullscreenControl = false;
      if (asl_configuration.streetviewcontrol == 'false') maps_params.streetViewControl = false;

      maps_params['fullscreenControlOptions'] = {
        position: google.maps.ControlPosition.RIGHT_CENTER
        //position: google.maps.ControlPosition.TOP_CENTER
        
      };
    
      if (asl_configuration.maxzoom && !isNaN(asl_configuration.maxzoom)) {
        maps_params['maxZoom'] = parseInt(asl_configuration.maxzoom);
      }

      if (asl_configuration.minzoom && !isNaN(asl_configuration.minzoom)) {
        maps_params['minZoom'] = parseInt(asl_configuration.minzoom);
      }

      map = new google.maps.Map(document.getElementById('asl-map-canv'), maps_params);

      window['asl_map'] = map;


      ///////Add a Map Reset Button////////////
      asl_configuration.reset_button = true;

      if (asl_configuration.reset_button) {

        var $reset_btn = null;

        function ASLResetMAP(controlDiv, map) {

          // Set CSS for the control border.
          $reset_btn = $('<span class="asl-reset-map" style="display:none">' + asl_configuration.words.reset_map + '</span>');
          controlDiv.appendChild($reset_btn[0]);

          // Setup the click event listeners: simply set the map to Chicago.
          $reset_btn[0].addEventListener('click', function() {

            map.panTo(new google.maps.LatLng(asl_lat, asl_lng));
            map.setZoom(parseInt(asl_configuration.zoom));

            $reset_btn[0].style.display = 'none';

            //Reset the Category
            if (asl_view._panel.$category_ddl) {

              var selected_cats = asl_view._panel.$category_ddl.val();
              if (selected_cats && selected_cats.length > 0) {
                asl_view._panel.$category_ddl.multiselect('deselect', asl_view._panel.$category_ddl.val(), true);
              }
            }
          });
        }

        var centerControlDiv = document.createElement('div');
        var centerControl = new ASLResetMAP(centerControlDiv, map);

        centerControlDiv.index = 1;
        //map.controls[google.maps.ControlPosition.LEFT].push(centerControlDiv);
        map.controls[google.maps.ControlPosition.RIGHT].push(centerControlDiv);

      }
      /////////////////


      if (asl_configuration.map_layout) {
        var map_style = eval('(' + asl_configuration.map_layout + ')');
        map.set('styles', map_style);
      }


      if (window['_asl_map_customize']) {

        _asl_map_customize = JSON.parse(_asl_map_customize);

        //ADd trafice layer
        if (_asl_map_customize.trafic_layer && _asl_map_customize.trafic_layer == 1) {

          trafic_layer = new google.maps.TrafficLayer();
          trafic_layer.setMap(map);
        }

        //ADd bike layer
        if (_asl_map_customize.bike_layer && _asl_map_customize.bike_layer == 1) {

          bike_layer = new google.maps.BicyclingLayer();
          bike_layer.setMap(map);
        }

        //ADd transit layer
        if (_asl_map_customize.transit_layer && _asl_map_customize.transit_layer == 1) {

          transit_layer = new google.maps.TransitLayer();
          transit_layer.setMap(map);
        }


        ///Load the DATA
        if (_asl_map_customize.drawing) {

          asl_drawing.loadData(_asl_map_customize.drawing, map);
        }
      }

      ///////////////////////////////////////////////////////////////////////
      var _features = [];

      /*Add all categories*/

      for (var i in asl_categories) {
        var cat = asl_categories[i];
        that.FEATURES_.add(new asl_locator.Feature(cat.category_id, cat.name, cat.icon, ((cat.ordr && !isNaN(cat.ordr))? parseInt(cat.ordr): 0)));
      }

      if (asl_configuration.load_all == '1') {

        that.fetch_remote_data();
      } else if (asl_configuration.load_all == '2') {

        var $reload_btn = null,
          _set_position = null,
          first_loaded = false;

        function ASLReloadMAP(controlDiv, map) {

          // Set CSS for the control border.
          $reload_btn = $('<span class="asl-reload-map" display="none"><i class="icon-arrows-cw"></i>' + asl_configuration.words.reload_map + '</span>');
          controlDiv.appendChild($reload_btn[0]);

          // Setup the click event listeners: simply set the map to Chicago.
          $reload_btn[0].addEventListener('click', function() {

            $reload_btn.find('i').addClass('animate-spin');
            that.fetch_remote_data(_set_position);
          });
        }

        var centerControlDiv = document.createElement('div');
        var centerControl = new ASLReloadMAP(centerControlDiv, map);

        centerControlDiv.index = 1;
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(centerControlDiv);

        //the load event
        google.maps.event.addListener(map, 'idle', function() {

          if (asl_view && asl_view.marker_clicked) {

            _set_position = asl_view.marker_center;
            asl_view.marker_clicked = false;
          }

          $reload_btn[0].style.display = 'block';

          //first load
          if (!first_loaded) {
            first_loaded = true;
            that.fetch_remote_data();
          }
        });
      }
      //  load_all == '0'
      else
        google.maps.event.addListener(map, 'idle', function() {

          //  fix over here
          var _set_position = null;

          if (asl_view && asl_view.marker_clicked) {

            _set_position = asl_view.marker_center;
            asl_view.marker_clicked = false;
            return;
          }


          that.fetch_remote_data(_set_position);
        });
    
    


    };
    /**
     * @const
     * @type {!asl_locator.FeatureSet}
     * @private
     */
    asl_engine.dataSource.prototype.FEATURES_ = new asl_locator.FeatureSet();


    /**
     * @return {!asl_locator.FeatureSet}
     */
    asl_engine.dataSource.prototype.getDSFeatures = function() {
      return this.FEATURES_;
    };


    /**
     * [parseData Parse Data]
     * @param  {[type]} rows [description]
     * @return {[type]}      [description]
     */
    asl_engine.dataSource.prototype.parseData = function(rows) {

      var stores = [];

      var current_hr_min = asl_date.getHours() + (asl_date.getMinutes() / 100),
        current_day      = asl_date.getDay();


      var _asl_categories = asl_categories;
      asl_categories      = {};
      var c_keys = Object.keys(_asl_categories);


      for (var c in c_keys) {

        if (typeof(_asl_categories[c_keys[c]]) == "object") {

          asl_categories[String(c_keys[c])] = _asl_categories[c_keys[c]];
          asl_categories[c_keys[c]].len = 0;
        }
      }

      //Calculate Open Hours
      var days      = { '1': 'mon', '2': 'tue', '3': 'wed', '4': 'thu', '5': 'fri', '6': 'sat', '0': 'sun' },
        day_keys    = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
        current_day = days[current_day];

      var time_now   = new Date;
      var days_number  = { 
        'sun': 0,
        'mon': 1,
        'tue': 2,
        'wed': 3,
        'thu': 4,
        'fri': 5,
        'sat': 6
      };

      /*
      var cust_lbls = [
      asl_configuration.words.carry_out,
      asl_configuration.words.dine_in,
      asl_configuration.words.delivery
      ];
      */

      //  Use to Get All Hours
      function getAllHours(row) {

        //Close and Open Timing Hourse
        if (row.open_hours) {


          row.open        = false;
          row.days        = [];
          row.open_hours  = JSON.parse(row.open_hours);
          var days_words  = asl_configuration.days;
          var all_days_hours = [];

          for (var _kd in day_keys) {

            var k_day   = day_keys[_kd],
              hours     = row.open_hours[k_day],
              is_today  = (k_day == current_day) ? true : false;

             //Loop and Match Open Hours
              if(hours == '1') {
                  
                  if(is_today)
                      row.open =  true;

                  //  Push the open day
                  row.days.push(days_words[k_day]);
              }
              else if(hours == '0') {
                  
                  if(is_today)
                      row.open =  false;
              }
              else if(hours) {
                  
                  //  console.log(row.title, '===> ' + k_day + ' ===> ', hours);
                  //  Push the open day
                  row.days.push(days_words[k_day]);

                  var day_hours   = [];


                  for(var h in hours) {

                      if(!hours.hasOwnProperty(h))continue;

                      var hr_span = hours[h].split(' - ');

                      //time per day hack
                      var start_time = hr_span[0],
                          end_time   = hr_span[1];

                      var _start = (start_time != 0)?asl_engine.helper.asl_timeConvert(start_time):0,
                          _end   = (end_time != 0)?asl_engine.helper.asl_timeConvert(end_time):24;

                      //hack for end
                      if(_end == 0)_end = 24;


                      /**
                       *
                       * Timing 
                       * "10:00 PM" - "1:00 AM"
                       *  22 duration 3
                       *
                       * "4:00 PM", "2:00 AM"
                       *  16 duration
                       * 
                       */
                      
                      //  Do all the testing when open is false
                      if(!row.open) {

                          //  Simple Checking, Normal timing
                          if(_start < _end) {

                              // Is Today is used as only Today timing will be opened and they will be considered only and avoid other days, also once open saved
                              if(!row.open && is_today) {

                                  row.open = (_start && _end)? asl_engine.helper.between(current_hr_min, _start, _end): false;
                              }
                          }
                          //  Complex Checking
                          else {

                              var duration =  24 - _start  + _end;

                              // Convert the times in the Dates
                              var from_date = new Date();
                              from_date.setHours(Math.floor(_start));
                              from_date.setMinutes(getDecimal(_start));

                              // Validate the Correct day
                              if(time_now.getDay() != days_number[k_day]) {
                                  from_date.subDays(1);
                              }


                              var to_date = new Date(from_date.getTime());
                              to_date.addHours(duration);
                              to_date.setMinutes(getDecimal(_end));

                              //  Validate if it is in asl_engine.helper.between the timing, set Open is true
                              if(time_now > from_date && time_now < to_date) {
                                  row.open = true;
                              }

                          }
                      }
                      
                      //  Change the Format if 24 hours setting
                      if(asl_configuration.time_24) {

                          
                          _start += 0.01;
                          _start  = parseFloat(_start).toFixed(2);
                          var _s_time = String(_start).split('.');
                          
                          _s_time[0]  = asl_engine.helper.asl_leadzero(parseInt(_s_time[0]));
                          _s_time[1]  = asl_engine.helper.asl_leadzero((parseInt(_s_time[1]) - 1));
                          start_time = _s_time.join(':');


                          _end += 0.01;
                          _end = parseFloat(_end).toFixed(2);

                          var _e_time = String(_end).split('.');
                          _e_time[0]  = asl_engine.helper.asl_leadzero(parseInt(_e_time[0]));
                          _e_time[1]  = asl_engine.helper.asl_leadzero((parseInt(_e_time[1]) - 1));
                          end_time = _e_time.join(':');
                      }
                      else {

                          var str_s_time = start_time.split(':'),
                              str_e_time = end_time.split(':');
                          

                          if(str_s_time[0]) {
                              str_s_time[0] = asl_engine.helper.asl_leadzero(parseInt(str_s_time[0]));
                          } 

                          start_time = str_s_time.join(':');

                          if(str_e_time[0]) {
                              str_e_time[0] = asl_engine.helper.asl_leadzero(parseInt(str_e_time[0]));
                          } 

                          end_time = str_e_time.join(':');
                      }

                      day_hours.push('<span>'+start_time+' - '+end_time+'</span>');

                      /*if(is_today) {
                          
                          row.start_time  = start_time;
                          row.end_time    = end_time;
                      }*/
                  }

                  all_days_hours.push('<span><span class="asl-day-lbl">'+days_words[k_day]+':</span><span class="asl-time-hrs">'+ day_hours.join('') + '</span></span>');
              }
          }
          //End of For loop

          row.days = row.days.join(', ');

          row.open_hours = (all_days_hours.length > 0)?('<span class="asl-week-hrs">'+all_days_hours.join('')+'</span>'):null;
        }
        else row.open =  true;

      };


            // Use to Get Today Hours
      function getTodayHours(row) {


        //Close and Open Timing Hourse
        if (row.open_hours) {

          row.open       = false;
          
          row.open_hours = JSON.parse(row.open_hours);

          // Hours of the current day
          var hours = row.open_hours[current_day];

          row.open_hours = null;

          //Loop and Match Open Hours
          if(hours == '1') {
              
              row.open        =  true;
              row.open_hours  = null;
          }
          else if(hours == '0') {
              
              row.open        =  false;
              row.open_hours  = null;
          }
          else if(hours) {
              
              row.open_hours = [];

              for(var h in hours) {

                  if(!hours.hasOwnProperty(h))continue;

                  var hr_span = hours[h].split(' - ');

                  //time per day hack
                  var start_time = hr_span[0];
                  var end_time   = hr_span[1];


                  
                  var _start = (start_time != 0)?asl_engine.helper.asl_timeConvert(start_time):0,
                      _end   = (end_time != 0)?asl_engine.helper.asl_timeConvert(end_time):24;

                  //hack for end
                  if(_end == 0)_end = 24;
                

                  //  Do all the testing when open is false
                  if(!row.open) {

                      //  Simple Checking, Normal timing
                      if(_start < _end) {

                          // Is Today is used as only Today timing will be opened and they will be considered only and avoid other days, also once open saved
                          if(!row.open) {

                              row.open = (_start && _end)? asl_engine.helper.between(current_hr_min, _start, _end): false;
                          }
                      }
                      //  Complex Checking
                      else {

                          var duration =  24 - _start  + _end;

                          // Convert the times in the Dates
                          var from_date = new Date();
                          from_date.setHours(Math.floor(_start));
                          from_date.setMinutes(getDecimal(_start));

                          // Validate the Correct day
                          if(time_now.getDay() != days_number[current_day]) {
                              from_date.subDays(1);
                          }


                          var to_date = new Date(from_date.getTime());
                          to_date.addHours(duration);
                          to_date.setMinutes(getDecimal(_end));

                          //  Validate if it is in asl_engine.helper.between the timing, set Open is true
                          if(time_now > from_date && time_now < to_date) {
                              row.open = true;
                          }
                      }
                  }

                  
                  //Change the format if 24 hours setting
                  if(asl_configuration.time_24) {

                      
                      _start += 0.01;
                      _start  = parseFloat(_start).toFixed(2);
                      var _s_time = String(_start).split('.');
                      
                      _s_time[0]  = asl_engine.helper.asl_leadzero(parseInt(_s_time[0]));
                      _s_time[1]  = asl_engine.helper.asl_leadzero((parseInt(_s_time[1]) - 1));
                      start_time = _s_time.join(':');


                      _end += 0.01;
                      _end = parseFloat(_end).toFixed(2);

                      var _e_time = String(_end).split('.');
                      _e_time[0]  = asl_engine.helper.asl_leadzero(parseInt(_e_time[0]));
                      _e_time[1]  = asl_engine.helper.asl_leadzero((parseInt(_e_time[1]) - 1));
                      end_time = _e_time.join(':');
                  }
                  else {

                      var str_s_time = start_time.split(':'),
                          str_e_time = end_time.split(':');
                      

                      if(str_s_time[0]) {
                          str_s_time[0] = asl_engine.helper.asl_leadzero(parseInt(str_s_time[0]));
                      } 

                      start_time = str_s_time.join(':');

                      if(str_e_time[0]) {
                          str_e_time[0] = asl_engine.helper.asl_leadzero(parseInt(str_e_time[0]));
                      } 

                      end_time = str_e_time.join(':');
                  }


                  row.open_hours.push(start_time+' - '+end_time);
              }

              row.open_hours = (row.open_hours.length > 0)?row.open_hours.join(' <br> '):null;
          }

        }
        else row.open =  true;

      };
/**
	 * Check user position with Google Maps geolocation API
	 * Get the user current position if available
	 */
 function checkUserPosition () {
 // var origin = asl_view.getMyPossition();
              // console.log(origin);
            navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            //var origin = lat , lng; 
            return lat , lng;
           // console.log(lat , lng);
             });
               //alert(origin);
          }

      var show_categories = asl_configuration.show_categories || asl_configuration.category_accordion;

      for (var i = 0; i < rows.length; i++) {

        var row = rows[i];

        row.id = parseInt(row.id);
        row.ordr = (!row.ordr || isNaN(row.ordr)) ? 0 : parseInt(row.ordr);
        row.lat = parseFloat(row.lat);
        row.lng = parseFloat(row.lng);
        row.logo_id = (row.logo_id && !isNaN(row.logo_id)) ? parseInt(row.logo_id) : row.logo_id;


        var position = new google.maps.LatLng(row.lat, row.lng);

        //When Open Hours
        row.open_hours = (row.open_hours) ? (row.open_hours) : null;


        if (!row.state) row.state = '';

        var locality = asl_engine.helper.implode([row.city, row.state, row.postal_code], ', ');
        var addr = [row.street, locality];

        row.address       = asl_engine.helper.implode(addr, '<br>');
        var _categories   = row.categories ? row.categories.split(",") : [],
          row_categories  = [];


        //If categories are enabled
        if (show_categories) {

          var c_names = [];
          var c_ids   = [];

          for (var k in _categories) {

            var _cat_id = _categories[k].toString();

            if (asl_categories[_cat_id]) {

              asl_categories[_cat_id].len++;
              //Add the Features
              row_categories.push(asl_categories[_cat_id]);
              c_names.push(asl_categories[_cat_id].name);
              c_ids.push(asl_categories[_cat_id].id);
              
            } else
              delete _categories[k];
          }


          //  Sort by Category
          row.cat = (c_names && c_names[0])? c_names[0]: '';
          

          row.c_ids   = c_ids;
          row.c_names = asl_engine.helper.implode(c_names, ', ');

          //julie category sort by __order
          row.categories = row_categories;
        }

        

        row.city = $.trim(row.city) /*.replace(asl_configuration.regex, '')*/ ;
        row.country = $.trim(row.country);

        if (!row.state)
          row.state = '';

        //No addiitional Info
        if (!asl_configuration.additional_info) {
          row.description_2 = null;
        }

        row.marker_id = (row.marker_id) ? row.marker_id.toString() : "";


        if (asl_configuration.week_hours)
          getAllHours(row);
        else
          getTodayHours(row);

        //Saving all the stores
        var store = new asl_locator.Store(row.id, position, _categories, row);
        stores.push(store);
      }

      return stores;
    };


    /*INITIALIZE THE Data Source*/
    var data_source = new asl_engine.dataSource();

    /**
     * [getStores Get Stores on Each Pan]
     * @param  {[type]}   bounds          [description]
     * @param  {[type]}   active_features [description]
     * @param  {Function} callback        [description]
     * @param  {[type]}   _no_ajax        [description]
     * @return {[type]}                   [description]
     */
    data_source.getStores = function(bounds, active_features, callback, _no_ajax) {

      var that    = this,
          stores  = [];

      var func_name = (asl_configuration.and_filter)? 'hasAllCategory': 'hasAnyCategory';

      //  Has any Filter Active
      for (var i = 0, store; store = this.stores_[i]; i++) {
        if (store[func_name](active_features)) {
          stores.push(store);
        }
      }

      callback(stores);
    };


    data_source.load_locator();


  })(jQuery, asl_underscore);
};


jQuery(document).ready(asl_store_locator);
