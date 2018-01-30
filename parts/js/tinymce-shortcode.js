(function($) 
{
  tinymce.create('tinymce.plugins.PiklistShortcodePlugin', {
    init: function(editor, url) 
    {
      // NOTE: This allows nested shortcodes with UI to be handled properly.
      editor.on('BeforeSetContent', function(event)
      {
        if (!event.content)
        {
          return;
        }

        event.content = wp.mce.views.setMarkers(event.content);
      });

      editor.on('PostProcess', function(event)
      {
        if (event.content)
        {
          event.content = event.content.replace(/<span [^>]*?data-wpview-text="([^"]+)"[^>]*>[\s\S]*?<\/span>/g, reset_views_callback);
        }
      });

      editor.on('BeforeAddUndo', function(event)
      {
        if (event.level.content)
        {
          event.level.content = reset_views(event.level.content);
        }
      });

      editor.on('PreProcess', function(event)
      {
        empty_view_nodes(event.node);
      }, true);

      editor.on('hide', function()
      {
        wp.mce.views.unbind();

        empty_view_nodes();
      });
      
      $.each(piklist.shortcodes, function(index, shortcode) 
      {
        if (shortcode.editor)
        {
          editor.addButton('shortcode-' + shortcode.shortcode, {
            title: shortcode.name,
            icon: 'dashicon dashicons ' + shortcode.icon + ' piklist-shortcode-button',
            role: shortcode.shortcode,
            onclick: function(event) 
            {
              var button = $(event.target).parents('.mce-btn:eq(0)');

              if (!button.hasClass('piklist-shortcode-button'))
              {
                button
                  .addClass('piklist-shortcode-button')
                  .find('.piklist-shortcode-button:eq(0)')
                  .removeClass('piklist-shortcode-button');
              }
            }
          });
        }
      });
    },
    
    getInfo: function() 
    {
      return {
        longname: 'Piklist Shortcode Plugin',
        author: 'Piklist',
        authorurl: 'https://piklist.com',
        infourl: 'https://piklist.com',
        version: tinymce.majorVersion + '.' + tinymce.minorVersion
      };
    }
  });
 
  tinymce.PluginManager.add('piklist_shortcode', tinymce.plugins.PiklistShortcodePlugin);
  
  function reset_views_callback(match, view) 
  {
    var shortcode = $(match).data('wpview-type');
  
    return piklist.shortcodes[shortcode].inline === true ? window.decodeURIComponent(view) : '<p>' + window.decodeURIComponent(view) + '<\/p>';
  }
  
  function reset_views(content) 
  {
    return content.replace(/<span[^>]+data-wpview-text="([^"]+)"[^>]*>(?:[\s\S]+?wpview-selection-after[^>]+>[^<>]*<\/p>\s*|\.)<\/span>/g, reset_views_callback);
  }
  
  function empty_view_nodes(root) 
  {
    $('span[data-wpview-text], p[data-wpview-marker]', root).each(function(i, node) 
    {
      node.innerHTML = '.';
    });
  }
  
  if (typeof wp.shortcode != 'undefined')
  {
    wp.shortcode.next = function(tag, text, index) 
    {
      var re = wp.shortcode.regexp(tag),
        match, result;

      re.lastIndex = index || 0;
      match = re.exec(text);
    
      if (!match) 
      {
        return;
      }
    
      // NOTE: Added to allow proper parsing of nest shortcodes that have UI
      var _text = text.substr(text.indexOf('[' + match[2] + match[3])),
        _match = _text.match(/\[\/([^\]]+)]/g),
        _open_tag, _open_match, _close_tag, _close_match;

      if (_match)
      {
        for (var i = 0; i < _match.length; i++)
        {
          _open_tag = new RegExp('\\[(\\[?)(' + _match[i].replace(/[\[\/\]']+/g, '') + ')', 'g');
          _open_match = _text.match(_open_tag);
        
          _close_tag = new RegExp('\\' + _match[i], 'g');
          _close_match = _text.match(_close_tag);
        
          if ((_open_match && !_close_match)
              || (!_open_match && _close_match)
              || (_open_match && _close_match && _open_match.length < _close_match.length)
             )
          {
            return;
          }
        }
      }
      // END
    
      // If we matched an escaped shortcode, try again.
      if ('[' === match[1] && ']' === match[7]) 
      {
        return wp.shortcode.next(tag, text, re.lastIndex);
      }
      
      result = {
        index: match.index,
        content: match[0],
        shortcode: wp.shortcode.fromMatch(match)
      };

      // If we matched a leading `[`, strip it from the match and increment the index accordingly.
      if (match[1]) 
      {
        result.content = result.content.slice(1);
        result.index++;
      }

      // If we matched a trailing `]`, strip it from the match.
      if (match[7]) 
      {
        result.content = result.content.slice(0, -1);
      }

      return result;
    };
  }
  
})(jQuery);