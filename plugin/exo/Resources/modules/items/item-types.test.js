import assert from 'assert'
import {ensure, mockTranslator} from '#/main/core/tests'
import {
  registerItemType,
  listItemMimeTypes,
  getDefinition,
  getDecorators,
  resetTypes
} from './../items/item-types'

describe('Registering an item type', () => {
  before(mockTranslator)
  afterEach(resetTypes)

  it('throws if item name type is absent or invalid', () => {
    assert.throws(() => {
      registerItemType({})
    }, /name is mandatory/)
    assert.throws(() => {
      registerItemType({name: []})
    }, /name must be a string/i)
  })

  it('throws if item mime type is absent or invalid', () => {
    assert.throws(() => {
      registerItemType({
        name: 'foo',
        player:{
          component: () => 'player',
          reduce: item => item
        },
        editor: {
          component: () => 'editor',
          reduce: item => item
        }
      })
    }, /mime type is mandatory/)
    assert.throws(() => {
      registerItemType({
        name: 'foo',
        type: [],
        player:{
          component: () => 'player',
          reduce: item => item
        },
        editor: {
          component: () => 'editor',
          reduce: item => item
        }
      })
    }, /mime type must be a string/i)
  })

  it('throws if item editor is absent', () => {
    assert.throws(() => {
      registerItemType({
        name: 'foo',
        type: 'foo/bar',
        player:{
          component: () => 'player',
          reduce: item => item
        }
      })
    }, /editor is mandatory/i)
  })

  it('throws if item editor component is absent', () => {
    assert.throws(() => {
      registerItemType({
        name: 'foo',
        type: 'foo/bar',
        player:{
          component: () => 'player',
          reduce: item => item
        },
        editor: {
          reduce: item => item
        }
      })
    }, /editor component is mandatory/i)
  })

  it('throws if item paper component is absent', () => {
    assert.throws(() => {
      registerItemType({
        name: 'foo',
        type: 'foo/bar',
        player:{
          component: () => 'player',
          reduce: item => item
        },
        editor: {
          component: () => 'editor',
          reduce: item => item
        }
      })
    }, /paper component is mandatory/i)
  })

  it('registers valid types as expected', () => {
    registerItemType(validDefinitionFixture())

    ensure.equal(listItemMimeTypes(), ['foo/bar'])

    const def = getDefinition('foo/bar')

    ensure.equal(def.name, 'foo')
    ensure.equal(def.type, 'foo/bar')
    ensure.equal(def.editor.component(), 'editor')
    ensure.equal(def.editor.reduce('item'), 'item')
    ensure.equal(def.player(), 'player')
  })

  it('throws if item type is already registered', () => {
    registerItemType(validDefinitionFixture())
    assert.throws(() => {
      registerItemType(validDefinitionFixture())
    }, /already registered/i)
  })

  it('defaults items to questions', () => {
    registerItemType(validDefinitionFixture())
    ensure.equal(getDefinition('foo/bar').question, true)
  })

  it('defaults decorators to identity functions', () => {
    registerItemType(validDefinitionFixture())
    ensure.equal(getDefinition('foo/bar').editor.decorate('item'), 'item')
  })

  it('defaults validators to noop functions', () => {
    registerItemType(validDefinitionFixture())
    ensure.equal(getDefinition('foo/bar').editor.validate('item'), {})
  })

  it('throws if definition contains extra properties', () => {
    const definition = Object.assign({}, validDefinitionFixture(), {bar: 'baz'})
    assert.throws(() => {
      registerItemType(definition)
    }, /unknown property 'bar' in 'foo' definition/i)
  })
})

describe('Getting a type definition', () => {
  before(mockTranslator)
  afterEach(resetTypes)

  it('throws if type does not exist', () => {
    assert.throws(() => {
      getDefinition('unknown/type')
    }, /unknown item type/i)
  })

  it('returns the full definition', () => {
    registerItemType(validDefinitionFixture())
    const def = getDefinition('foo/bar')
    ensure.equal(def.type, 'foo/bar')
    ensure.equal(def.editor.component(), 'editor')
    ensure.equal(def.editor.reduce('item'), 'item')
    ensure.equal(def.editor.decorate('item'), 'item')
    ensure.equal(def.editor.validate('item'), {})
    ensure.equal(def.player(), 'player')
  })
})

describe('Getting type decorates', () => {
  before(mockTranslator)
  afterEach(resetTypes)

  it('sorts decorators by type', () => {
    registerItemType(validDefinitionFixture())
    const decorators = getDecorators()
    ensure.equal(Object.keys(decorators), ['foo/bar'])
    ensure.equal(typeof decorators['foo/bar'], 'function')
  })
})

function validDefinitionFixture() {
  return {
    name: 'foo',
    type: 'foo/bar',
    editor: {
      component: () => 'editor',
      reduce: item => item
    },
    player: () => 'player',
    paper: () => 'paper'
  }
}
