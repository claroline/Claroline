import merge from 'lodash/merge'
import validate from './validators'
import {registerItemType, resetTypes} from './../../items/item-types'
import {ensure, mockTranslator} from '#/main/core/tests'

describe('quiz validator', () => {
  before(mockTranslator)

  it('returns no errors on valid quiz', () => {
    const quiz = {
      parameters: {
        pick: 1,
        duration: 2,
        maxAttempts: 3,
        maxAttemptsPerDay: 0,
        maxPapers: 5
      }
    }
    ensure.equal(validate.quiz(quiz), {})
  })

  it('returns validation errors if invalid', () => {
    const quiz = {
      parameters: {
        pick: null,
        duration: 'foo',
        maxAttempts: -3
      }
    }
    ensure.equal(validate.quiz(quiz), {
      parameters: {
        pick: 'This value should not be blank.',
        duration: 'This value should be a valid number.',
        maxAttempts: 'This value should be 0 or more.',
        maxAttemptsPerDay: 'This value should not be blank.',
        maxPapers: 'This value should not be blank.'
      }
    })
  })
})

describe('step validator', () => {
  it('returns no errors on valid step', () => {
    const step = {
      parameters: {
        maxAttempts: 3
      }
    }
    ensure.equal(validate.step(step), {})
  })

  it('returns validation errors if invalid', () => {
    const step = {
      parameters: {
        maxAttempts: -3
      }
    }
    ensure.equal(validate.step(step), {
      parameters: {
        maxAttempts: 'This value should be 0 or more.'
      }
    })
  })
})

describe('item validator', () => {
  afterEach(resetTypes)

  it('checks base item properties', () => {
    registerFixtureType()
    const item = {
      id: '1',
      type: 'foo/bar',
      content: ''
    }
    ensure.equal(validate.item(item), {
      content: 'This value should not be blank.'
    })
  })

  it('delegates to item type validator', () => {
    registerFixtureType({
      editor: {
        validate: item => {
          return item.foo !== 'bar' ? {foo: 'Should be bar'} : {}
        }
      }
    })
    const item = {
      id: '1',
      type: 'foo/bar',
      content: 'Question?',
      foo: false
    }
    ensure.equal(validate.item(item), {
      foo: 'Should be bar'
    })
  })

  it('merges base and type errors', () => {
    registerFixtureType({
      editor: {
        validate: item => {
          return item.foo !== 'bar' ? {foo: 'Should be bar'} : {}
        }
      }
    })
    const item = {
      id: '1',
      type: 'foo/bar',
      content: '',
      foo: 'baz'
    }
    ensure.equal(validate.item(item), {
      content: 'This value should not be blank.',
      foo: 'Should be bar'
    })
  })

  it('returns an empty object when validation succeeds', () => {
    registerFixtureType()
    const item = {
      id: '1',
      type: 'foo/bar',
      content: 'Question'
    }
    ensure.equal(validate.item(item), {})
  })
})

function registerFixtureType(properties = {}) {
  return registerItemType(merge(
    {
      name: 'foo',
      type: 'foo/bar',
      editor: {
        component: {},
        reduce: item => item
      },
      player: {
        component: {},
        reduce: item => item
      },
      paper: {}
    },
    properties
  ))
}
