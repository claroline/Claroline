const validator = require('./validator')
const assert = require('assert')
const actionFail = require('./test-stubs/actions-fail')
const actionPass = require('./test-stubs/actions-pass')

describe('Configuration', () => {
  it('Test the action validation process', () => {
    function executeError() {
      validator.validate(actionFail)
    }

    assert.throws(executeError, Error, 'Error thrown')
    assert.equal(validator.validate(actionPass), true)
  })
})
