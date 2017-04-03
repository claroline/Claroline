#!/usr/bin/env node

/* global require, process */

// Theme utilities
const themesFinder = require('../config/finder')
const themesBuilder = require('../build')

// Current script version
const CURRENT_VERSION = 'v1.0.0'

// Get command arguments
// remove node executable & current script path
const commandArgs = process.argv.slice(2)

if (getFlag(commandArgs, 'version', 'v')) {
  // Show version
  console.log(CURRENT_VERSION)
} else if (getFlag(commandArgs, 'help', 'h')) {
  // Show help
  console.log('Usage: npm run themes \n')
  console.log('Arguments: ')
  console.log('  --no-cache            (-nc) : all files will be forced recompiled without checking cache')
  console.log('  --only-default        (-d)  : only rebuild claroline default themes')
  console.log('  --only-custom         (-c)  : only rebuild custom themes')
  console.log('  --theme=path/to/theme (-t)  : rebuild a theme located at a custom path')
  console.log('                                mostly for when your theme is not in the regular custom themes directories')
} else {
  // Run command
  // remove command name from args list
  const commandName = commandArgs.shift()
  switch (commandName) {
    /**
     * BUILD COMMAND.
     */
    case 'build':
      // Read command args
      const noCache     = getFlag(commandArgs, 'no-cache', 'nc')
      const onlyDefault = getFlag(commandArgs, 'only-default', 'd')
      const onlyCustom  = getFlag(commandArgs, 'only-custom', 'c')
      const theme       = getParameter(commandArgs, 'theme', 't')

      console.log(!noCache ?
        'Run `build` command with **cache**.' :
        'Run `build` command with **no cache**.'
      )

      // Validate args
      assert(theme && onlyDefault,      'You can not set \'--only-default|-d\' when you provide a custom theme path.')
      assert(theme && onlyCustom,       'You can not set \'--only-custom|-c\' when you provide a custom theme path.')
      assert(onlyDefault && onlyCustom, 'You can not set \'--only-default|-d\' and \'--only-custom|-c\' at the same time.')

      // Retrieve the correct themes
      var themesToBuild
      if (theme) {
        console.log('Rebuild custom theme : ' + theme + '.')
        themesToBuild = [themesFinder.getThemeFromPath(theme)]
      } else if (!onlyDefault && !onlyCustom) {
        console.log('Rebuild all themes (default + custom).')
        themesToBuild = themesFinder.getPlatformThemes()
      } else if (onlyDefault) {
        console.log('Rebuild only default themes.')
        themesToBuild = themesFinder.getDefaultThemes()
      } else {
        console.log('Rebuild only custom themes.')
        themesToBuild = themesFinder.getCustomThemes()
      }

      // Checks additional args
      assert(
        0 !== commandArgs.length,
        `Unrecognized command parameters : ${commandArgs.join(', ')}.`
      )

      themesBuilder.build(themesToBuild, noCache)

      break

    // Unknown command
    default:
      console.error(`Unknown command "${commandName}."`)
      break
  }
}

function getFlag(args, longName, shortName) {
  const longNamePos = args.indexOf('--'+longName)
  if (-1 !== longNamePos) {
    args.splice(longNamePos, 1)

    return true
  }

  const shortNamePos = args.indexOf('-'+shortName)
  if (-1 !== shortNamePos) {
    args.splice(shortNamePos, 1)

    return true
  }

  return false
}

function getParameter(args, longName, shortName) {
  var parameter = null

  const regex = new RegExp('(--'+longName+'=|-'+shortName+')["\']?(.+)["\']?')
  for (var i = 0; i < args.length; i++) {
    var matches = args[i].match(regex)
    if (matches && matches.length !== 0 && matches[2]) {
      parameter = matches[2]
      args.splice(i, 1)

      break
    }
  }

  return parameter
}

function assert(condition, message) {
  if (condition) {
    console.error(message)
    process.exit()
  }
}
