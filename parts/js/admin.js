/* --------------------------------------------------------------------------------
  Updates or enhancements to Piklist Functionality
--------------------------------------------------------------------------------- */

;(function($, window, document, undefined)
{
  'use strict';

  $(document).ready(function()
  {
    $('body').wptabs();

    piklist_admin.init();
  });

  var piklist_admin = {

    init: function()
    {
      piklist_admin.body_class();
      piklist_admin.meta_boxes();
      piklist_admin.post_name();
      piklist_admin.post_submit_meta_box();
      piklist_admin.thickbox();
      piklist_admin.user_forms();
      piklist_admin.tag_forms();
      piklist_admin.empty_elements();
      piklist_admin.list_tables();
      piklist_admin.widgets();
      piklist_admin.customizer();
      piklist_admin.shortcodes();
      piklist_admin.notices();
      piklist_admin.pointers();
      piklist_admin.add_ons();
    },

    body_class: function()
    {
      if (window.location != window.parent.location)
      {
        $('body').addClass('piklist-in-iframe');
      }
    },

    notices: function()
    {
      $(document).on('click', '.notice.is-dismissible > .notice-dismiss', function(event)
      {
        $.ajax({
          type: 'POST',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'piklist_notice',
            id: $(this).parent().attr('id')
          }
        });
      });
    },

    pointers: function()
    {
      $.each(piklist.pointers, function(index)
      {
        var pointer_id = this.pointer_id,
          options = $.extend(this.options, {
            close: function()
            {
              $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                  action: 'dismiss-wp-pointer',
                  pointer: pointer_id
                }
              });
            }
          });

        $(this.target)
          .pointer(options)
          .pointer('open');
      });
    },

    add_ons: function()
    {
      if ($('.piklist-field-add-on-button').length > 0)
      {
        $('.piklist-field-add-on-button').click(function(event)
        {
          event.preventDefault();

          if ($(this).hasClass('button-primary'))
          {
            $(this).removeClass('button-primary').addClass('button').text('Disable').prev(':checkbox').attr('checked', true);
            $(this).next('a').show();
          }
          else
          {
            $(this).removeClass('button').addClass('button-primary').text('Activate').prev(':checkbox').attr('checked', false);
            $(this).next('a').hide();
          }
        });
      }
    },

    empty_elements: function()
    {
      $('#post-body-content').each(function()
      {
        if ($.trim($(this).html()) == '')
        {
          $(this).html('');
        }
      });
    },

    tag_forms: function()
    {
      $('body.wp-admin.edit-tags-php form#addtag :submit').on('click', function(event)
      {
        $('body.wp-admin.edit-tags-php form#addtag :input#tag-name').on('change', function(event)
        {
          window.location.href = window.location.href;
        });
      });
    },

    user_forms: function()
    {
      if ('WebkitAppearance' in document.documentElement.style)
      {
        setTimeout(function()
        {
          $('input:-webkit-autofill').each(function()
          {
            var name = $(this).attr('name');

            $(this).after(this.outerHTML).remove();
            $('input[name=' + name + ']').val('');
          });
        }, 250);
      }
    },

    thickbox: function()
    {
      var frame;

      $(document).on('click', 'body.piklist-in-iframe .piklist-upload-file-button, body.piklist-in-iframe .wp-media-buttons button', function()
      {
        $('#TB_iframeContent', window.parent.document).css({
          'position': 'absolute'
          ,'top': 0
          ,'left': 0
          ,'height': $('#TB_window', window.parent.document).height()
        });

        setTimeout(function()
        {
          var frame = typeof wp.media.frames.file_frame != 'undefined' ? wp.media.frames.file_frame : wp.media.frame;

          frame.on('close', function()
          {
            $('#TB_iframeContent', window.parent.document).css({
              'position': 'relative'
              ,'top': 'auto'
              ,'left': 'auto'
              ,'height': $('#TB_window', window.parent.document).height() - $('#TB_title', window.parent.document).height()
            });
          });
        }, 100);
      });

      $(document).on('click', '.piklist-list-table-export-button', function()
      {
        setTimeout(function()
        {
          var TB_WIDTH = 870,
            TB_HEIGHT = 800;

          $('#TB_window').css({
            marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px'
            ,width: TB_WIDTH + 'px'
            ,height: TB_HEIGHT + 'px'
            ,marginTop: '-' + parseInt((TB_HEIGHT / 2), 10) + 'px'
          });

          $('#TB_ajaxContent').css({
            height: TB_HEIGHT - 45 + 'px'
          })
        }, 100);
      });
    },

    meta_boxes: function()
    {
      $('.piklist-meta-box-collapse:not(.piklist-meta-box-lock)').addClass('closed');

      $('.piklist-meta-box-lock')
        .addClass('stuffbox')
        .css('box-shadow', 'none')
        .find('.handlediv')
          .hide()
          .next('h3.hndle')
            .removeClass('hndle')
            .css('cursor', 'default');

      $('.piklist-meta-box-lock').each(function()
      {
        if (!$(this).hasClass('hide-if-js'))
        {
          $(this).show();
        }

        $(this)
          .find('h2.hndle')
          .removeClass('hndle')
          .addClass('piklist-meta-box-hndle');
      });

      $('.piklist-meta-box > .inside').each(function()
      {
        if ($(this).find(' > *:first-child').hasClass('piklist-field-container'))
        {
          $(this).css({
            'margin-top': '0'
          });
        }
      });
    },

    post_name: function()
    {
      var form = $('body.wp-admin.post-php form#post:first');

      if (form.length > 0)
      {
        var slug = form.find(':input#post_name');

        if (slug.length <= 0)
        {
          form.append($('<input type="hidden" name="post_name" id="post_name">'));
        }
      }
    },

    post_submit_meta_box: function()
    {
      if (typeof piklist.post != 'undefined' && typeof piklist.post_statuses != 'undefined' && $('div#submitdiv.postbox').length > 0)
      {
        var post_status = $('select#post_status'),
          draft = false,
          publish = false,
          default_status;

        $(':input#original_post_status').val(piklist.post.post_status);

        post_status.children().each(function()
        {
          var value = $(this).val();

          if (typeof piklist.post_statuses != 'undefined')
          {
            $(this).remove();
          }
        });

        for (var status in piklist.post_statuses)
        {
          post_status.append('<option value="' + status + '" ' + (status == piklist.post.post_status ? 'selected="selected"' : '') + '>' + piklist.post_statuses[status].label + '</option>');

          if (status == 'publish')
          {
            publish = true;
          }
          else if (status == 'draft')
          {
            draft = true;
          }

          if (!default_status)
          {
            default_status = piklist.post_statuses[status];
          }
        }

        $('span#post-status-display').text(piklist.post.post_status != 'auto-draft' && typeof piklist.post_statuses[piklist.post.post_status] != 'undefined' ? piklist.post_statuses[piklist.post.post_status].label : default_status.label)

        if (!publish)
        {
          if ($('div#submitdiv.postbox > h3 > span').text() == window.postL10n.publish)
          {
            $('div#submitdiv.postbox > h3 > span').text('Update');
          }

          $(':input#publish')
            .attr('name', 'save')
            .val('Update');

          window.postL10n.publish = 'Update';
        }

        if (!draft)
        {
          $(':input#save-post').remove();
        }

        $('.save-post-status, .cancel-post-status', '#post-status-select').on('click', function(event)
        {
          event.preventDefault();

          var status = post_status.val(),
            text = post_status.find('> option:selected').val();

          if (status != 'draft')
          {
            $('#hidden_post_status, #original_publish').val(text);
          }
        });

        $('#publish', '#major-publishing-actions').on('click', function()
        {
          if ($('#post-status-select').css('display') != 'none')
          {
            $('.save-post-status', '#post-status-select').trigger('click');
          }

          if ($('#post-visibility-select').css('display') != 'none')
          {
            $('.save-post-visibility', '#post-visibility-select').trigger('click');
          }
        });
      }
    },

    shortcodes: function()
    {
      $.each(piklist.shortcodes, function(index, shortcode)
      {
        if (wp.mce.views)
        {
          var tag = typeof shortcode.shortcode != 'undefined' ? shortcode.shortcode : shortcode;

          wp.mce.views.register(tag, piklist_admin.shortcode(tag));
        }
      });

      $(window).on('resize', function()
      {
        var thickbox = $('#TB_iframeContent');

        if (thickbox.length > 0 && thickbox.attr('src').indexOf('page=shortcode_editor'))
        {
          var width = Math.round($(window).width() - 60),
            height = Math.round($(window).height() - 60);

          $('#TB_window')
            .css({
              'width': width,
              'height': height,
              'top': '50%',
              'margin-left': -width / 2,
              'margin-top': -height / 2
            })
            .find('iframe')
              .css({
                'width': '100%',
                'height': height - $('#TB_title').height()
              });
        }
      });

      $(document).on('click', '.piklist-shortcode-button', function(event)
      {
        event.preventDefault();

        var title = $(this).prop('title'),
          editor = typeof parent.tinymce.activeEditor != 'undefined' ? parent.tinymce.activeEditor : false,
          frame_in_frame = window.location != window.parent.location,
          content = editor && !frame_in_frame ? editor.selection.getContent({format : 'html'}) : null,
          url = location.href,
          editor_url = url.substr(0, url.indexOf('/wp-')) + '/wp-admin/admin.php',
          attributes = {
            page: 'shortcode_editor'
          };

        if ($(this).hasClass('mce-btn'))
        {
          title = $(this).attr('aria-label');

          attributes[piklist.prefix + 'shortcode_data[name]'] = $(this).attr('role');
          attributes[piklist.prefix + 'shortcode_data[action]'] = 'insert';
        }
        else if ($(this).hasClass('mce-ico'))
        {
          title = $(this).parents('.mce-btn').attr('aria-label');

          attributes[piklist.prefix + 'shortcode_data[name]'] = $(this).parents('.mce-btn').attr('role');
          attributes[piklist.prefix + 'shortcode_data[action]'] = 'insert';
        }
        else if ($(this).data('piklist-shortcode'))
        {
          attributes[piklist.prefix + 'shortcode_data[name]'] = $(this).data('piklist-shortcode');
          attributes[piklist.prefix + 'shortcode_data[action]'] = 'insert';
        }

        if (typeof pagenow != 'undefined' && $.inArray(pagenow, ['post', 'post-new']) > -1)
        {
          attributes[piklist.prefix + 'post[ID]'] = $('#post_ID').val();
        }

        attributes[piklist.prefix + '[admin_hide_ui]'] = 'true';

        if (content && !frame_in_frame)
        {
          attributes[piklist.prefix + 'shortcode_data[content]'] = content;
        }

        attributes['TB_iframe'] = 'true';

        tb_show(title, editor_url + '?' + $.param(attributes));

        $(window, parent).trigger('resize');
      });

      // Handle auto-select of first only shortcode to allow edit view to function properly
      $(document).on('beforePreWpautop', function(event)
      {
        if (!wp.autosave)
        {
          var editor = tinyMCE.activeEditor;

          editor.selection.select(editor.getBody(), true);

          editor.selection.collapse(true);
        }
      });

      if ($('body.admin_page_shortcode_editor').length > 0)
      {
        if (piklist.validate_check)
        {
          var data = $(':input[name^="' + piklist.prefix + 'shortcode_data["]').serializeArray(),
            shortcode = {
              attrs: {},
              type: 'single'
            },
            attribute = null,
            attribute_length,
            attribute_string = '',
            output = '';

          for (var i = 0; i < data.length; i++)
          {
            attribute = data[i].name.replace(piklist.prefix + 'shortcode_data[', '').replace(/[\[\]']+/g, '');

            if (attribute == 'content')
            {
              shortcode.content = parent.switchEditors._wp_Nop(data[i].value).replace(new RegExp('\\\\', 'g'), '');

              shortcode.type = 'closed';
            }
            else if (attribute == 'name')
            {
              shortcode.tag = data[i].value;
            }
          }

          data = $(':input[name^="' + piklist.prefix + 'shortcode["]').serializeArray();

          for (var i = 0; i < data.length; i++)
          {
            attribute_length = (piklist.prefix + 'shortcode').length + 1;
            attribute = data[i].name.substr(attribute_length, data[i].name.indexOf(']') - attribute_length);

            if (attribute.toLowerCase() != 'id')
            {
              attribute_string += attribute + '="' + encodeURIComponent(data[i].value) + '" ';
            }
          }

          shortcode.attrs = wp.shortcode.attrs(attribute_string);

          if (typeof piklist.shortcodes[shortcode.tag] != 'undefined' && (piklist.shortcodes[shortcode.tag].type == 'closed' || piklist.shortcodes[shortcode.tag].type == 'single'))
          {
            shortcode.type = piklist.shortcodes[shortcode.tag].type;
          }

          output = wp.shortcode.string(shortcode);

          var _output = $(document).triggerHandler('piklist:shortcode:insert', [output, shortcode]);
          if (typeof _output != 'undefined')
          {
            output = _output;
          }

          parent.send_to_editor(output);

          // NOTE: In order to make sure nested shortcodes are rendered properly we have to toggle the views
          parent.switchEditors.go(parent.tinymce.activeEditor.id, 'html');
          parent.switchEditors.go(parent.tinymce.activeEditor.id, 'tmce');

          parent.tb_remove();
        }
        else
        {
          $('ul.piklist-shortcodes > .attachment').on('click', function(event)
          {
            $('input[name="' + piklist.prefix + 'shortcode_data[name]"]').val($(this).data('piklist-shortcode'));

            var data = $(':input[name^="' + piklist.prefix + 'shortcode_data["]').serializeArray(),
              post_id = typeof pagenow != 'undefined' && $.inArray(pagenow, ['post', 'post-new']) > -1 ? '&' + piklist.prefix + 'post[ID]=' + $('#post_ID').val() : null,
              attributes = {};

            $.each(data, function(key, value)
            {
              attributes[value['name']] = value['value'];
            });

            window.location.href = window.location.href + (window.location.href.indexOf('?') > -1 ? '&' : '?') + $.param(attributes) + (post_id ? post_id : '');
          });
        }
      }
    },

    shortcode: function(shortcode)
    {
      return {
        template: wp.media.template('piklist-shortcode'),

        getContent: function()
        {
          if (piklist.shortcodes[this.shortcode.tag].preview === true)
          {
            var is_IE = typeof tinymce != 'undefined' ? tinymce.Env.ie : false,
              preview = $('<iframe/>'),
              id = 'piklist-shortcode-preview-' + this.shortcode.tag + '-' + Math.random().toString(36).substr(2, 16);

            preview
              .attr('id', id)
              .attr('src', is_IE ? 'javascript:""' : '')
              .attr('frameBorder', '0')
              .attr('allowTransparency', 'true')
              .attr('scrolling', 'no')
              .addClass('piklist-shortcode-preview')
              .css({
                'width': '100%',
                'height': '1',
                'display': 'block'
              });

            $.ajax({
              type: 'POST',
              url: ajaxurl,
              dataType: 'json',
              data: {
                action: 'piklist_shortcode',
                shortcode: wp.shortcode.string(this.shortcode),
                post_id: $('#post_ID').val(),
                preview_id: id
              },
              success: function(response)
              {
                $('.wp-editor-wrap iframe').each(function()
                {
                  var preview = $(this).contents().find('iframe#' + response.data.preview_id);

                  if (preview.length > 0)
                  {
                    var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver,
                      head = preview.contents().find('head'),
                      body = preview.contents().find('body'),
                      resize = function()
                      {
                        try {
                          preview.height(preview.contents().find('html').outerHeight());
                        } catch (error) {}
                      };

                    $(this).contents().find('head').find('link').each(function()
                    {
                      head.append($(this).prop('outerHTML'));
                    });

                    body
                      .css('min-height', '100%')
                      .html(response.data.html)
                      .find('a')
                      .attr('target', '_blank');

                    if (MutationObserver)
                    {
                      var observer = new MutationObserver(function()
                      {
                        preview.contents().find('img, link').load(resize);

                        resize();
                      });

                      observer.observe(preview.contents().find('body')[0], {
                        attributes: true,
                        childList: true,
                        subtree: true
                      });
                    }
                    else
                    {
                      for (var i = 1; i < 6; i++)
                      {
                        setTimeout(resize, i * 700);
                      }
                    }

                    setTimeout(resize, 700);
                  }
                });
              }
            });

            return preview.prop('outerHTML');
          }
          else if (piklist.shortcodes[this.shortcode.tag].inline === true)
          {
            var preview = [];

            this.template = wp.media.template('piklist-shortcode-inline');

            if (this.shortcode.content != '')
            {
              preview.push(this.shortcode.content);
            }
            else
            {
              for (var attribute in this.shortcode.attrs.named)
              {
                preview.push(this.shortcode.attrs.named[attribute] + ' (' + attribute + ')');
              }
            }

            return this.template({
              tag: this.shortcode.tag,
              attributes: this.shortcode.attrs.named,
              preview: preview,
              options: typeof piklist.shortcodes[this.shortcode.tag] != 'undefined' ? piklist.shortcodes[this.shortcode.tag] : {
                name: this.shortcode.tag.replace(/_/g, ' ').toUpperCase()
              }
            });
          }

          return this.template({
            tag: this.shortcode.tag,
            attributes: this.shortcode.attrs.named,
            options: typeof piklist.shortcodes[this.shortcode.tag] != 'undefined' ? piklist.shortcodes[this.shortcode.tag] : {
              name: this.shortcode.tag.replace(/_/g, ' ').toUpperCase()
            }
          });
        },

        replaceMarkers: function()
        {
          this.getMarkers(function(editor, node)
          {
            var selected = node === editor.selection.getNode(),
              $view_node;

            if (!this.loader && $(node).text() !== this.text)
            {
              editor.dom.setAttrib(node, 'data-wpview-marker', null);
              return;
            }

            if (piklist.shortcodes[this.type].inline === true)
            {
              var prev_node = $(node).prev(),
                next_node = $(node).next(),
                prev_html = typeof prev_node != 'undefined' && typeof prev_node.html() != 'undefined' ? prev_node.html() : '',
                next_html = typeof next_node != 'undefined' && typeof next_node.html() != 'undefined' ? next_node.html() : '',
                tag = typeof prev_node != 'undefined' ? prev_node.prop('tagName') : next_node.prop('tagName');

              tag = tag ? tag : 'p';

              $view_node = editor.$(
                '<' + tag + '>' +
                  (prev_html + (prev_html != '' && prev_html.substr(prev_html.length - 6) != '&nbsp;' ? '&nbsp;' : '')) +
                  '<span class="wpview wpview-wrap wpview-wrap-inline" data-wpview-text="' + this.encodedText + '" data-wpview-type="' + this.type + '" contenteditable="false">' +
                    '<span class="wpview-body">' +
                      '<span class="wpview-content wpview-type-' + this.type + '"></span>' +
                    '</span>' +
                  '</span>' +
                  ((next_html != '' && next_html.substr(0, 6) != '&nbsp;' ? '&nbsp;' : '') + next_html) +
                '</' + tag + '>'
              );

              editor.$(prev_node).remove();
              editor.$(next_node).remove();

              editor.$(node).replaceWith($view_node);
            }
            else
            {
              $view_node = editor.$(
                '<div class="wpview wpview-wrap" data-wpview-text="' + this.encodedText + '" data-wpview-type="' + this.type + '" contenteditable="false">' +
                  '<p class="wpview-selection-before">\u00a0</p>' +
                  '<div class="wpview-body">' +
                    '<div class="wpview-content wpview-type-' + this.type + '"></div>' +
                  '</div>' +
                  '<p class="wpview-selection-after">\u00a0</p>' +
                '</div>'
              );

              editor.$(node).replaceWith($view_node);
            }

            if (selected && typeof editor.wp != 'undefined' && typeof editor.wp.setViewCursor != 'undefined')
            {
              editor.wp.setViewCursor(false, $view_node[0]);
            }
          });
        },

        edit: function(string)
        {
          if (typeof string === 'object')
          {
            string = decodeURIComponent($(string).data('wpview-text'));
          }

          var shortcode = {},
            regex = wp.shortcode.regexp(this.type);

          regex.lastIndex = 0;

          var match = regex.exec(string);

          if (match)
          {
            shortcode = {
              attrs: wp.shortcode.attrs(match[3]),
              tag: this.type,
              content: match[5],
              type: typeof match[6] != 'undefined' ? 'closed' : 'single'
            };

            var editor = typeof window.tinymce.activeEditor != 'undefined' ? window.tinymce.activeEditor : false,
              editor_iframe = $(editor.getDoc()),
              editor_url = location.href.substr(0, location.href.indexOf('/wp-')) + '/wp-admin/admin.php',
              index = $(editor_iframe.find('*[data-wpview-type="' + this.type + '"]')).index(editor_iframe.find('*[data-wpview-type="' + this.type + '"][data-mce-selected="1"]')),
              attributes = {
                'page': 'shortcode_editor'
              };

            if (typeof pagenow != 'undefined' && $.inArray(pagenow, ['post', 'post-new']) > -1)
            {
              attributes[piklist.prefix + 'post[ID]'] = $('#post_ID').val();
            }

            attributes[piklist.prefix + '[admin_hide_ui]'] = 'true';

            $.each(shortcode.attrs.named, function(key, value)
            {
              attributes[piklist.prefix + 'shortcode[' + key + ']'] = value;
            });

            attributes[piklist.prefix + 'shortcode_data[name]'] = shortcode.tag;
            attributes[piklist.prefix + 'shortcode_data[action]'] = 'update';
            attributes[piklist.prefix + 'shortcode_data[index]'] = index < 0 ? false : index;

            if (typeof shortcode.content != 'undefined')
            {
              attributes[piklist.prefix + 'shortcode_data[content]'] = switchEditors._wp_Nop(shortcode.content);
            }

            attributes['TB_iframe'] = 'true';

            for (var key in attributes)
            {
              if ($.type(attributes[key]) == 'string')
              {
                attributes[key] = attributes[key].replace(/\\\'/g, '\'');
                attributes[key] = escape(attributes[key]);
              }
            }

            tb_show('Edit ' + (piklist.shortcodes[this.type].name ? piklist.shortcodes[this.type].name : null), editor_url + '?' + $.param(attributes));

            $(window).trigger('resize');
          }
        },

        indexesOf: function(string, regex)
        {
          var match,
            indexes = [];

          regex = new RegExp(regex);

          while (match = regex.exec(string))
          {
            indexes.push(match.index);
          }

          return indexes;
        }
      };
    },

    customizer: function()
    {
      if ($('body').hasClass('wp-customizer'))
      {
        $('.widget-tpl').on('click', function()
        {
          setTimeout(piklist_admin.customizer_widget_setup, 100);
        });

        setTimeout(piklist_admin.customizer_widget_setup, 100);

        $(document).on('widget-synced', function(event, widget, form)
        {
          if (widget.parent().attr('id').indexOf('customize-control-widget' + piklist.prefix + 'piklist-universal-widget') > -1)
          {
            var widget_container = widget.find('.widget-content:first'),
              widget_inside = widget.find('.widget-inside:first');

            widget_inside.css('top', 0);

            widget_container
              .css({
                'visibility': 'hidden',
                'max-height': '100%'
              })
              .html(form)

            widget_container
              .removeData('wptabs')
              .removeData('piklistgroups')
              .removeData('piklistcolumns')
              .removeData('piklistmediaupload')
              .removeData('piklistaddmore')
              .removeData('piklistfields');

            setTimeout(function()
            {
              widget_container
                .find('.piklist-universal-widget-form-container')
                .wptabs()
                .piklistgroups()
                .piklistcolumns()
                .piklistmediaupload()
                .piklistaddmore({
                  sortable: true
                })
                .piklistfields();

                widget_container.css({
                  'visibility': 'visible',
                  'overflow': 'visible'
                });
            }, 50);
          }
        });
      }
    },

    customizer_widget_setup: function()
    {
      $('.piklist-universal-widget-select').each(function()
      {
        $(this)
          .parents('.widget-content:eq(0)')
          .off('change input propertychange');
      });
    },

    widgets: function()
    {
      $(document).on('mousedown', '.widget input[name="savewidget"]', function()
      {
        var button = $(this),
          widget_container = button.parents('.widget-control-actions:first').siblings('.widget-content:first'),
          widget_title = button.parents('.widget').find('.widget-title h4'),
          title = button.parents('form').find('.piklist-universal-widget-form-container').data('widget-title');

        $('.piklist-universal-widget-form-container').on('remove', function()
        {
          widget_container
            .css({
              'height': widget_container.outerHeight(),
              'overflow': 'hidden'
            });

          widget_container
            .removeData('wptabs')
            .removeData('piklistgroups')
            .removeData('piklistcolumns')
            .removeData('piklistmediaupload')
            .removeData('piklistaddmore')
            .removeData('piklistfields');

          setTimeout(function()
          {
            widget_container
              .find('.piklist-universal-widget-form-container')
              .wptabs()
              .piklistgroups()
              .piklistcolumns()
              .piklistmediaupload()
              .piklistaddmore({
                sortable: true
              })
              .piklistfields();

            if (typeof title != 'undefined')
            {
              widget_title
                .find('.in-widget-title')
                .text(':  ' + title);
            }

            widget_container
              .css({
                'height': 'auto',
                'overflow': 'visible'
              });
          }, 50);
        });

        if (typeof tinyMCE != 'undefined')
        {
          tinyMCE.triggerSave();

          widget_container.find('.wp-editor-area').each(function()
          {
            if (typeof switchEditors != 'undefined')
            {
              switchEditors.go($(this).attr('id'), 'tmce');
            }
          });
        }
      });

      piklist_admin.widget_title();

      $(document).on('change', '.piklist-universal-widget-select', function(event)
      {
        var widget = $(this).val(),
          addon = $(this).data('piklist-addon'),
          action = ('piklist-universal-widget-' + addon).replace(/-/g, '_'),
          widget_container = $(this).parents('.widget-content'),
          widget_classes = $(this).attr('class').split(' '),
          widget_form = widget_container.find('.piklist-universal-widget-form-container'),
          widget_number = $(this).attr('name').split('[')[1].replace(/\]/g, ''),
          widget_title = $(this).parents('.widget').find('.widget-title h4'),
          widget_description = widget_container.find('.piklist-universal-widget-select-container p'),
          wptab_active = widget_container.data('piklist-wptab-active');

        if (widget)
        {
          widget_form
            .hide()
            .empty();

          $.ajax({
            type : 'POST',
            url : ajaxurl,
            async: false,
            dataType: 'json',
            data: {
              action: action,
              widget: widget,
              number: widget_number
            }
            ,success: function(response)
            {
              widget_title
                .find('.in-widget-title')
                .text(':  ' + response.widget.data.title)

              widget_description.text(response.widget.data.description);

              widget_form
                .removeData('wptabs')
                .removeData('piklistgroups')
                .removeData('piklistcolumns')
                .removeData('piklistmediaupload')
                .removeData('piklistaddmore')
                .removeData('piklistfields');

              widget_form
                .html(response.form)
                .wptabs()
                .piklistgroups()
                .piklistcolumns()
                .piklistmediaupload()
                .piklistaddmore({
                  sortable: true
                })
                .piklistfields();

              widget_container
                .find('.wp-tab-bar > li')
                .removeClass('wp-tab-active');

              widget_container
                .find('.wp-tab-bar > li:first')
                .addClass('wp-tab-active');

              if (widget_container.find('.wp-tab-bar').length > 0 && typeof wptab_active != 'undefined')
              {
                widget_container
                  .find('.wp-tab-bar > li')
                  .removeClass('wp-tab-active')
                  .get(2)
                  .addClass('wp-tab-active');
              }

              piklist_admin.widget_dimensions(widget_container, response.widget.data.height, response.widget.data.width);
            }
          });
        }
      });

      $('.wp-tab-bar li a').on('click', function(event)
      {
        var widget_container = $(this).parents('.widget-content:first');

        if (widget_container.length > 0)
        {
          widget_container.attr('data-piklist-wptab-active', $(this).text());
        }
      });

      piklist_admin.widget_title();
    },

    widget_title: function()
    {
      setTimeout(function()
      {
        $('.piklist-universal-widget-form-container').each(function()
        {
          var widget_container = $(this).parents('.widget-content'),
            widget_title = $(this).parents('.widget').find('.widget-title h4'),
            title = $(this).data('widget-title'),
            height = $(this).data('widget-height'),
            width = $(this).data('widget-width');

          if (typeof title != 'undefined')
          {
            widget_title
              .find('.in-widget-title')
              .text(':  ' + title);
          }

          piklist_admin.widget_dimensions(widget_container, height, width);
        });
      }, 250);
    },

    widget_dimensions: function(widget, height, width)
    {
      var container = widget.parents('.widget:first'),
        inside = container.find('.widget-inside'),
        toggle = container.find('.widget-action:first'),
        toggled = false;

      if (inside.is(':visible'))
      {
        toggle.trigger('click');

        toggled = true;
      }

      widget
        .siblings('input[name="widget-width"]')
        .val(width ? width : 250);

      widget
        .siblings('input[name="widget-height"]')
        .val(height ? height : 200);

      if ($('body.wp-customizer').length > 0)
      {
        inside
          .find('.widget-content')
          .css({
            'width': width,
            'max-width': width
          })
          .attr('style', 'max-width: ' + width + ' !important');
      }

      setTimeout(function()
      {
        widget
          .find('.piklist-universal-widget-form-container')
          .show();

        if (toggled)
        {
          toggle.trigger('click');
        }
      }, 250);
    },

    list_tables: function()
    {
      $('.piklist-list-table-export-columns')
        .sortable()
        .disableSelection();

      $('.piklist-list-table-export-submit').on('click', function(event)
      {
        var form_id = $(this).attr('rel');

        tb_remove();

        $('#' + form_id).submit();
      });
    }
  };



  /* --------------------------------------------------------------------------------
    WordPress Updates
  -------------------------------------------------------------------------------- */

  // NOTE: Allow meta boxes and widgets to have tinymce
  $(document)
    .on('sortstart', '.ui-sortable', function(event, ui)
    {
      if ($(this).is('.ui-sortable') && typeof tinyMCE != 'undefined')
      {
        $(this).find('.wp-editor-area.piklist-field-element').each(function()
        {
          var id = $(this).attr('id'),
            command = tinymce.majorVersion == 3 ? 'mceRemoveControl' : 'mceRemoveEditor';

          tinyMCE.execCommand(command, false, id);
        });
      }
    })
    .on('sortstop sortreceive', '.ui-sortable', function(event, ui)
    {
      if ($(this).is('.ui-sortable') && typeof tinyMCE != 'undefined')
      {
        $(this).find('.wp-editor-area.piklist-field-element').each(function()
        {
          var id = $(this).attr('id'),
            command = tinymce.majorVersion == 3 ? 'mceAddControl' : 'mceAddEditor';

          tinyMCE.execCommand(command, false, id);
        });
      }
    });



  /* --------------------------------------------------------------------------------
    WP Tabs - Updates or enhancements to existing WordPress Functionality
  -------------------------------------------------------------------------------- */

  var WPTabs = function(element, options)
  {
    this.$element = $(element);
    this._init();
  };

  WPTabs.prototype = {

    constructor: WPTabs,

    _init: function()
    {
      this.setup();

      $('.wp-tab-bar li a').on('click', function(event)
      {
        event.preventDefault();

        var tab = $(this).closest('li'),
          index = $(this).closest('.wp-tab-bar').children().index(tab),
          panels = $(this).closest('.wp-tab-bar').nextUntil('.wp-tab-bar', '.wp-tab-panel');

        tab.addClass('wp-tab-active').siblings().removeClass('wp-tab-active');

        for (var i = 0; i < panels.length; i++)
        {
          $(panels[i]).toggle(i == index ? true : false);
        }
      });
    },

    setup: function()
    {
      $('.wp-tab-bar li a').each(function()
      {
        var tab = $(this).closest('li');

        if (!tab.hasClass('wp-tab-active'))
        {
          var index = $(this).closest('.wp-tab-bar').children().index(tab);

          $(this).closest('.wp-tab-bar').nextUntil('.wp-tab-bar', '.wp-tab-panel').eq(index).hide();
        }
      });
    }
  };

  $.fn.wptabs = function(option)
  {
    var _arguments = Array.apply(null, arguments);
    _arguments.shift();

    return this.each(function()
    {
      var $this = $(this),
        data = $this.data('wptabs'),
        options = typeof option === 'object' && option;

      if (!data)
      {
        $this.data('wptabs', (data = new WPTabs(this, $.extend({}, $.fn.wptabs.defaults, options, $(this).data()))));
      }

      if (typeof option === 'string')
      {
        data[option].apply(data, _arguments);
      }
    });
  };

  $.fn.wptabs.defaults = {};

  $.fn.wptabs.Constructor = WPTabs;

})(jQuery, window, document);
