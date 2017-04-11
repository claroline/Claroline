import React from 'react'
import {shallow} from 'enzyme'
import {spyConsole, renew, ensure} from '#/main/core/tests'
import {SubSection} from './sub-section.jsx'

describe('<SubSection/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(SubSection, 'SubSection')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<SubSection/>)
    ensure.missingProps(
      'SubSection',
      ['hidden', 'showText', 'hideText', 'toggle', 'children']
    )
  })

  it('has typed props', () => {
    shallow(
      <SubSection
        hidden="foo"
        showText={123}
        hideText={false}
        toggle="bar"
      >
        Bar
      </SubSection>
    )
    ensure.invalidProps(
      'SubSection',
      ['hidden', 'showText', 'hideText', 'toggle']
    )
  })

  it('if hidden, renders a link to show section', () => {
    let toggled = false

    const section = shallow(
      <SubSection
        hidden={true}
        showText="Show section"
        hideText="Hide section"
        toggle={() => toggled = true}
      >
        Bar
      </SubSection>
    )

    ensure.propTypesOk()

    const showLink = section.childAt(0)
    ensure.equal(showLink.name(), 'a')
    ensure.equal(showLink.text(), 'Show section')
    showLink.simulate('click')
    ensure.equal(toggled, true)
  })

  it('if shown, renders a link to hide section', () => {
    let toggled = false

    const section = shallow(
      <SubSection
        hidden={false}
        showText="Show section"
        hideText="Hide section"
        toggle={() => toggled = true}
      >
        Bar
      </SubSection>
    )

    ensure.propTypesOk()

    const hideLink = section.find('a')
    ensure.equal(hideLink.length, 1)
    ensure.equal(hideLink.text(), 'Hide section')
    hideLink.simulate('click')
    ensure.equal(toggled, true)
  })
})
