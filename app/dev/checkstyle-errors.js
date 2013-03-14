// Simplified checkstyle reporter, reporting only errors (not warnings)
// Orginal author: Boy Baukema
// http://github.com/relaxnow

/* node: true */

module.exports = {
	reporter: function (results) {
		"use strict";

		var files = {},
			out = [],
			pairs = {
				"&": "&amp;",
				"\"": "&quot;",
				"'": "&apos;",
				"<": "&lt;",
				">": "&gt;"
			},
			fileName, i, issue;

		function encode(s) {
			for (var r in pairs) {
				if (typeof(s) !== "undefined") {
					s = s.replace(new RegExp(r, "g"), pairs[r]);
				}
			}
			return s || "";
		}

		results.forEach(function (result) {
			// Register the file
			result.file = result.file.replace(/^\.\//, "");
			if (!files[result.file]) {
				files[result.file] = [];
			}

			// Add the error
			files[result.file].push({
				severity: "error",
				line: result.error.line,
				column: result.error.character,
				message: result.error.reason,
				source: result.error.raw
			});
		});

		out.push("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
		out.push("<checkstyle version=\"4.3\">");

		for (fileName in files) {
			if (files.hasOwnProperty(fileName)) {
				out.push("\t<file name=\"" + fileName + "\">");
				for (i = 0; i < files[fileName].length; i++) {
					issue = files[fileName][i];
					out.push(
						"\t\t<error " +
							"line=\"" + issue.line + "\" " +
							"column=\"" + issue.column + "\" " +
							"severity=\"" + issue.severity + "\" " +
							"message=\"" + encode(issue.message) + "\" " +
							"source=\"" + encode(issue.source) + "\" " +
							"/>"
					);
				}
				out.push("\t</file>");
			}
		}

		out.push("</checkstyle>");

		process.stdout.write(out.join("\n") + "\n");
	}
};