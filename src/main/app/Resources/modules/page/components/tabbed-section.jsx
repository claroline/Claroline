import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {PageSection} from '#/main/app/page/components/section'
import {Tab, Tabs} from '#/main/app/components/tabs'
import {Badge} from '#/main/app/components/badge'

/**
 * Creates a section with tabs.
 * ATTENTION: it uses the Router to define the opened tab. YOU CAN HAVE ONLY ONE IN A PAGE !
 */
const PageTabbedSection = (props) =>
  <PageSection {...omit(props, 'tabs', 'defaultTab')}>
    <Tabs defaultActiveKey={props.defaultTab ? props.defaultTab : props.tabs[0].name} variant="underline">
      {(props.tabs || [])
        .filter((tab) => undefined === tab.displayed || tab.displayed)
        .map(tab =>
          <Tab
            key={tab.name}
            eventKey={tab.name}
            title={<>
              {tab.title}
              {(tab.badge || 0 === tab.badge) &&
                <Badge className="icon-with-text-left" subtle={true} variant={0 !== tab.badge ? 'primary': 'secondary'}>{tab.badge}</Badge>
              }
            </>}
            disabled={tab.disabled}
          >
            {tab.component && createElement(tab.component)}
            {tab.render && tab.render()}
          </Tab>
        )
      }
    </Tabs>
  </PageSection>

PageTabbedSection.propTypes = {
  ...PageSection.propTypes,
  defaultTab: T.string,
  tabs: T.arrayOf(T.shape({
    name: T.string.isRequired,
    title: T.string,
    displayed: T.bool,
    disabled: T.bool,
    badge: T.node,
    render: T.func,
    component: T.element
  })).isRequired
}

export {
  PageTabbedSection
}
