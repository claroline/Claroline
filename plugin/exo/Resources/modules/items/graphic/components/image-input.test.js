import React from 'react'
import {shallow} from 'enzyme'
import {spyConsole, renew, ensure} from '#/main/core/tests'
import {ImageInput} from './image-input.jsx'

describe('<ImageInput/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(ImageInput, 'ImageInput')
  })
  afterEach(spyConsole.restore)

  it('renders a button', () => {
    const input = shallow(
      <ImageInput onSelect={() => {}}/>
    )
    ensure.propTypesOk()
    ensure.equal(input.children().length, 2)
    ensure.equal(input.childAt(1).name(), 'button')
  })
})
