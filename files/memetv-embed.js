/*jslint devel: true, eqeq: true, sub: true, passfail: true, nomen: true, plusplus: true, continue: true, regexp: true, maxerr: 1, indent: 2, todo: true, unparam: true */
/*global jQuery, tinymce, window */
(function ($) {
  'use strict';

  /**
   * Validates inserted URL
   */
  var $overlay,
    $popup,
    $form,
    $content,
    sizes = [
      {name: 'small', desc: '320x320', checked: true},
      {name: 'medium', desc: '480x480'},
      {name: 'large', desc: '600x600'}
    ],

    validateVideoUrl = function (url) {
      if (!url) {
        return false;
      }
      return (/^https?:\/\/(.*\.)?(memetv\.com)\/(meme|m)\/([a-zA-Z0-9]+)(\/.*)?$/).test(url);

    },

    openPopup = function () {
      if ($overlay && $popup && $content) {
        $overlay.show();
        $popup.show();

        $content.css('position', 'absolute');
        $content.css('top', Math.max(0, (($(window).height() - $content.outerHeight()) / 2) +
                                                    $(window).scrollTop()) + "px");
        $content.css('left', Math.max(0, (($(window).width() - $content.outerWidth()) / 2) +
                                                    $(window).scrollLeft()) + "px");
      }
    },

    closePopup = function () {
      if ($overlay && $popup && $form) {
        $overlay.hide();
        $popup.hide();
        $form[0].reset();
      }
    },

    /**
     * Displays settings popup
     */
    memetvembedPopup = function (url, ed) {
      var name = 'memetvembedSettings',
        sizesString = '',
        i;

      for (i = 0; i < sizes.length; i++) {
        sizesString += '<input type="radio" name="' + name + '_size" value="' + sizes[i].name + '" ' + (sizes[i].checked ? 'checked="checked"' : '') + ' /> ' + sizes[i].name + ' <span>(' + sizes[i].desc + ')</span> ';
      }


      $popup = $('#' + name);

      if ($popup.length == 0) {
        $popup = $('<div id="' + name + '" />');
        $overlay = $('<div id="' + name + '_overlay" />');
        $content = $('<div id="' + name + '_content">' +
            '<div class="titlebar">MemeTV Embed Settings <a href="#" id="' + name + '_close">X</a></div>' +
            '<div class="' + name + '_settings">' +
              '<form id="' + name + '_form" method="post">' +
              '<fieldset>' +
                '<p>' +
                  '<label>Meme Page URL:</label>' +
                  '<input type="text" value="http://" id="' + name + '_input" name="' + name + '_url" />' +
                  '<br /><span>eg. http://memetv.com/meme/1ta/vintage-jlo/</span>' +
                '</p>' +
                '<p>' +
                  '<label>Video Size:</label>' +
                  sizesString +
                '</p>' +
              '</fieldset>' +
              '<input type="submit" id="' + name + '_save" value="Insert Code" />' +
              '</form>' +
            '</div>' +
          '</div>');


        $overlay.appendTo($popup);
        $content.appendTo($popup);
        $('#' + name + '_close', $content).click(function () {
          closePopup();
          return false;
        });
        $popup.appendTo('body');

        $form = $('#' + name + '_form');

        $form.submit(function () {
          var videourl = $.trim($('input[name=' + name + '_url]', $(this)).val()) || null,
            size = $('input[name=' + name + '_size]:checked', $(this)).val() || null;

          if (url && size && url != '' && validateVideoUrl(videourl)) {
            ed.execCommand('mceInsertContent', false, '[memetv url="' + videourl + '" size="' + size + '" autoplay="0"]');
            closePopup();
          } else {
            alert('Please verify the URL you inserted');
          }

          return false;
        });

      }

      openPopup();

    };

  /**
   * Creates additional TinyMCE button
   */
  tinymce.create('tinymce.plugins.memetvembed', {
    init: function (ed, url) {
      ed.addButton('memetvembed', {
        title : 'Insert meme',
        image : url + '/memetv-embed.png',
        onclick : function () {
          memetvembedPopup(url, ed);

        }
      });

    },
    createControl: function (n, cm) {
      return null;

    },
    getInfo: function () {
      return {
        longname : 'memeTV',
        author : 'memetv',
        authorurl : 'http://www.memetv.com',
        infourl : 'http://www.memetv.com/plugins/wordpress/memetv-embed',
        version : "1.0"
      };

    }
  });

  tinymce.PluginManager.add('memetvembed', tinymce.plugins.memetvembed);


}(jQuery));
