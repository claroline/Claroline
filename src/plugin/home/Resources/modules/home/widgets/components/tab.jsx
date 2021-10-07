import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {Widget} from '#/main/core/widget/player/components/widget'

import {HomePage} from '#/plugin/home/tools/home/containers/page'
import {Tab as TabTypes} from '#/plugin/home/prop-types'

const WidgetsTab = props => {
  let visibleWidgets = get(props.currentTab, 'parameters.widgets', [])
    .filter(widget => widget.visible === true)

  return (
    <HomePage
      tabs={props.tabs}
      currentTab={props.currentTab}
      title={props.title}
    >
      {0 === visibleWidgets.length &&
        <ContentPlaceholder
          size="lg"
          icon="fa fa-frown-o"
          title={trans('no_section')}
        />
      }

      {0 !== visibleWidgets.length &&
        <div className="widgets-grid" style={{marginTop: 20}}>
          {visibleWidgets.map((widget, index) =>
            <Widget
              key={index}
              widget={widget}
              currentContext={props.currentContext}
            />
          )}
        </div>
      }
    </HomePage>
  )
}

WidgetsTab.propTypes = {
  currentContext: T.object,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  title: T.string.isRequired,
  currentTab: T.shape(
    TabTypes.propTypes
  )
}

export {
  WidgetsTab
}
