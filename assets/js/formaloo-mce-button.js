(function() {
  tinymce.PluginManager.add('formaloo_mce_button', function( editor, url ) {
      editor.addButton('formaloo_mce_button', {
                  text: 'formaloo',
                  icon: false,
                  onclick: function() {
                    // change the shortcode as per your requirement
                    editor.insertContent('[formaloo_shortcode]');
                 }
        });
  });
})();