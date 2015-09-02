(function() {
  tinymce.create('tinymce.plugins.widgetanywhere', {
    init : function(ed, url) {
      ed.addCommand('mceWidgetAnywhere', function() {
        ed.windowManager.open({
          file : url + '/widget-anywhere.html',
          width : 480,
          height : 160,
          inline : 1,
          title : 'Add Widget'
        }, {});
      });
      ed.addButton('widgetanywhere', {
        cmd : 'mceWidgetAnywhere',
        image : url + '/widget-anywhere.png'
      });
    },
    getInfo : function() {
      return {
        longname : 'Widget Anywhere Plugin',
        author : 'tenKinetic',
        authorurl : 'http://tenkinetic.com',
        infourl : 'http://tenkinetic.com/widget-anywhere',
        version : "1.0"
      };
    }
  });
  tinymce.PluginManager.add('widgetanywhere', tinymce.plugins.widgetanywhere);
})();