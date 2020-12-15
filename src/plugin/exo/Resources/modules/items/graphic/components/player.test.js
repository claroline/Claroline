import React from 'react'
import {mount} from 'enzyme'

import {spyConsole, renew, ensure} from '#/main/core/scaffolding/tests'

import {SHAPE_RECT, SHAPE_CIRCLE} from '#/plugin/exo/items/graphic/constants'
import {utils} from '#/plugin/exo/items/graphic/utils'
import {GraphicPlayer} from '#/plugin/exo/items/graphic/player'

describe('Graphic player', () => {
  describe('findArea', () => {
    it('detects points inside rectangles', () => {
      ensure.equal(utils.findArea({x: 100, y: 300}, solutionsFixture()), solutionsFixture()[0])
      ensure.equal(utils.findArea({x: 100, y: -300}, solutionsFixture()), undefined)
    })

    it('detects points inside circles', () => {
      ensure.equal(utils.findArea({x: 900, y: 1150}, solutionsFixture()), solutionsFixture()[1])
      ensure.equal(utils.findArea({x: 200, y: 1000}, solutionsFixture()), undefined)
    })
  })
})

describe('<GraphicPlayer/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(GraphicPlayer, 'GraphicPlayer')
  })
  afterEach(spyConsole.restore)

  it('renders an image', () => {
    const player = mount(
      <GraphicPlayer
        item={{
          image: {
            data: 'data:foo.jpg;qdsfqsd454545',
            width: 200
          },
          pointers: 0
        }}
        onChange={() => {}}
      />
    )
    ensure.propTypesOk()
    ensure.equal(player.find('img').length, 1)
  })
})

function solutionsFixture() {
  return [
    {
      area: {
        shape: SHAPE_RECT,
        coords: [
          {x: 50, y: 200},
          {x: 150, y: 500}
        ]
      }
    },
    {
      area: {
        shape: SHAPE_CIRCLE,
        center: {x: 800, y: 1000},
        radius: 200
      }
    }
  ]
}
