/* eslint no-console: "off" */

import assert from 'assert'
import diff from 'json-diff'
import escapeRegExp from 'lodash/escapeRegExp'

export const ensure = {
  equal: assertEqual,
  propTypesOk: assertPropTypesOk,
  missingProps: assertMissingProps,
  invalidProps: assertInvalidProps
}

export const spyConsole = {
  watch: watchConsole,
  restore: restoreConsole
}

// assign a random name to a component, ensuring it isn't cached and thus prop
// types are always checked
// (see https://github.com/facebook/react/issues/7047#issuecomment-228614964)
export function renew(component, name) {
  component.displayName = `${name}-${Math.random().toString()}`
}

// define a global noop Routing
export function mockRouting() {
  window.Routing = {generate: (...args) => args[0]}
}

// define a global noop Translator
export function mockTranslator() {
  window.Translator = {trans: msg => msg}
}

// define a global noop tinymce
export function mockTinymce() {
  window.tinymce = { get: () => ({
    on: () => {},
    setContent: () => {},
    destroy: () => {}
  })}
}

// deep equality test with a nice diff
export function assertEqual(actual, expected, message) {
  try {
    assert.deepStrictEqual(actual, expected, message)
  } catch (e) {
    const msg = message || ''
    e.message = `${msg}\n${diff.diffString(e.expected, e.actual)}`
    throw e
  }
}

// spy on console.error and stores error messages
function watchConsole() {
  const originalError = console.error
  console._errors = []
  console.error = msg => console._errors.push(msg)
  console._restore = () => {
    console.error = originalError
    delete console._errors
    delete console._restore
  }
}

// restore a previously watched console
function restoreConsole() {
  if (typeof console._restore !== 'function') {
    throw new Error(
      'Cannot restore console: console has not been watched or has already been restored'
    )
  }

  console._restore()
}

// assert no prop types errors where issued
function assertPropTypesOk() {
  assertEqual(
    extractPropTypesWarnings(),
    [],
    'Failed asserting that no PropTypes warnings were issued'
  )
}

// assert prop types errors about missing props where issued
function assertMissingProps(componentName, propNames) {
  assertPropTypesErrors(
    propNames,
    'missing',
    `The prop .+ is marked as required in \`${componentName}(\-|\`)`,
    name => `prop \`${name}\``
  )
}

// assert prop types errors about invalid props where issued
function assertInvalidProps(componentName, propNames) {
  assertPropTypesErrors(
    propNames,
    'invalid',
    `Invalid prop .+ supplied to \`${componentName}(\-|\`)`,
    name => `Invalid prop \`${name}\``
  )
}

function assertPropTypesErrors(propNames, criterium, componentRegex, makeErrorRegex) {
  const warnings = extractPropTypesWarnings()
  const componentPropsWarnings = warnings.filter(warning => {
    return warning.match(new RegExp(componentRegex))
  })
  const notFound = propNames.filter(name => {
    return !componentPropsWarnings.find(warning => {
      return warning.match(makeErrorRegex(escapeRegExp(name)))
    })
  })
  assertEqual([], notFound, `Failed asserting some props were ${criterium}`)
  assert(
    componentPropsWarnings.length === propNames.length,
    `Failed asserting only specified props were ${criterium}:\n${componentPropsWarnings.join('\n')})`
  )
}

function extractPropTypesWarnings() {
  if (typeof console._restore !== 'function') {
    throw new Error(
      'Cannot check for prop types warnings: console has not been watched or has already been purged'
    )
  }

  return console._errors.filter(error => /(Invalid prop|Failed prop)/.test(error))
}
