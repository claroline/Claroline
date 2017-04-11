/* eslint no-console: "off" */

import assert from 'assert'
import diff from 'json-diff'
import escapeRegExp from 'lodash/escapeRegExp'

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

// assert no prop types errors where issued
export function assertPropTypesOk() {
  assertEqual(
    extractPropTypesWarnings(),
    [],
    'Failed asserting that no PropTypes warnings were issued'
  )
}

// assert prop types errors about missing props where issued
export function assertMissingProps(componentName, propNames) {
  assertPropTypesErrors(
    propNames,
    'missing',
    `The prop .+ is marked as required in \`${componentName}(\-|\`)`,
    name => `prop \`${name}\``
  )
}

// assert prop types errors about invalid props where issued
export function assertInvalidProps(componentName, propNames) {
  assertPropTypesErrors(
    propNames,
    'invalid',
    `Invalid prop .+ supplied to \`${componentName}(\-|\`)`,
    name => `Invalid prop \`${name}\``
  )
}

export function assertPropTypesErrors(propNames, criterion, componentRegex, makeErrorRegex) {
  const warnings = extractPropTypesWarnings()
  const componentPropsWarnings = warnings.filter(warning => {
    return warning.match(new RegExp(componentRegex))
  })
  const notFound = propNames.filter(name => {
    return !componentPropsWarnings.find(warning => {
      return warning.match(makeErrorRegex(escapeRegExp(name)))
    })
  })
  assertEqual([], notFound, `Failed asserting some props were ${criterion}`)
  assert(
    componentPropsWarnings.length === propNames.length,
    `Failed asserting only specified props were ${criterion}:\n${componentPropsWarnings.join('\n')})`
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
