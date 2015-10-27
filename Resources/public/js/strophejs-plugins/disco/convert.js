var fs = require('fs'), sys = require('sys');

if (process.argv.length !== 3) {
	sys.puts(process.argv[1] + " file.xml");
}
fs.readFile(process.argv[2],'ascii', function(err, x) {
	sys.puts('var stanza = "";');
	x.split('\n').forEach(function(line) {
		sys.puts('stanza += "' + line + '";');
	});
});


