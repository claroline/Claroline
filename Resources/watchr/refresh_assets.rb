rootDir = File.expand_path(File.dirname(File.dirname(__FILE__))) + "/../../../../.."

watch( '../views/(.+)\.twigjs' ) {
    require 'fileutils'
    webJsDir = "#{rootDir}/web/js"
    FileUtils.rm_rf("#{webJsDir}/.", :secure => true)
    system("php #{rootDir}/app/console assetic:dump")
}

watch( '../less/bootstrap/.+' ) {
    system("php #{rootDir}/app/console claroline:themes:compile")
}

watch( '../less/themes/([^/]+)/.+' ) {
    |md| system("php #{rootDir}/app/console claroline:themes:compile --theme=#{md[1]}")
}