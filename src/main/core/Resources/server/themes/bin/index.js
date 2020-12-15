#!/usr/bin/env node

/* global require, process */

const path = require('path')
const shell = require('shelljs')

// Theme utilities
const themeConf = require('../config/theme')
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
  console.log('  --theme=path/to/theme (-t)  : rebuild a theme instance')
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
      const theme       = getParameter(commandArgs, 'theme', 't')

      console.log(!noCache ?
        'Run `build` command with **cache**.' :
        'Run `build` command with **no cache**.'
      )

      // Validate args
      assert(!theme, 'Theme path (-t) is required.')
      // Checks additional args
      assert(0 !== commandArgs.length, `Unrecognized command parameters : ${commandArgs.join(', ')}.`)

      // Retrieve the correct themes
      themesBuilder.build(getThemeFromPath(theme), noCache)

      break

    // Unknown command
    default:
      console.error(`Unknown command "${commandName}."`)
      break
  }
}

/**
 * Gets a theme from a custom path.
 *
 * @param {string} themePath
 *
 * @return {Theme}
 */
function getThemeFromPath(themePath) {
  if (!shell.test('-e', themePath) && !shell.test('-e', themePath+'.less')) {
    throw new Error(`Theme '${themePath}' not found.`)
  }

  return new themeConf.Theme(path.basename(themePath, '.less'), path.dirname(themePath))
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
