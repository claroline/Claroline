import merge from 'lodash/merge'
import validate from './validators'
import {registerItemType, resetTypes} from './../../items/item-types'
import {assertEqual} from './../../utils/test'

describe('quiz validator', () => {
  it('returns no errors on valid quiz', () => {
    const quiz = {
      title: 'foo',
      parameters: {
        pick: 1,
        duration: 2,
        maxAttempts: 3
      }
    }
    assertEqual(validate.quiz(quiz), {})
  })

  it('returns validation errors if invalid', () => {
    const quiz = {
      title: null,
      parameters: {
        pick: null,
        duration: 'foo',
        maxAttempts: -3
      }
    }
    assertEqual(validate.quiz(quiz), {
      title: 'This value should not be blank.',
      parameters: {
        pick: 'This value should not be blank.',
        duration: 'This value should be a valid number.',
        maxAttempts: 'This value should be 0 or more.'
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
    assertEqual(validate.step(step), {})
  })

  it('returns validation errors if invalid', () => {
    const step = {
      parameters: {
        maxAttempts: -3
      }
    }
    assertEqual(validate.step(step), {
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
    assertEqual(validate.item(item), {
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
    assertEqual(validate.item(item), {
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
    assertEqual(validate.item(item), {
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
    assertEqual(validate.item(item), {})
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
