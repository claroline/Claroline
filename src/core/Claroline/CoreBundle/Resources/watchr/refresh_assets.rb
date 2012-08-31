root = File.expand_path(File.dirname(File.dirname(__FILE__))) + "/../../../../.."

watch( '../views/(.+)\.twigjs' )        { system("php #{root}/app/console assetic:dump") }
watch( '../less/bootstrap/.+' )         { system("php #{root}/app/console claroline:themes:compile") }
watch( '../less/themes/([^/]+)/.+' )    { |md| system("php #{root}/app/console claroline:themes:compile --theme=#{md[1]}") }