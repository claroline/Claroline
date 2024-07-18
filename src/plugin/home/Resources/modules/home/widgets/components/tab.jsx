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
      root={props.root}
      currentTab={props.currentTab}
      title={props.title}
    >
      {0 === visibleWidgets.length &&
        <ContentPlaceholder
          className="my-3 flex-fill"
          size="lg"
          icon="fa fa-face-frown"
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
  root: T.bool,
  currentContext: T.object,
  title: T.string.isRequired,
  currentTab: T.shape(
    TabTypes.propTypes
  )
}

export {
  WidgetsTab
}
