import React, {Component} from 'react'
import classes from 'classnames'
import flatten from 'lodash/flatten'
import uniq from 'lodash/uniq'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/core/translation'
import {Widget as WidgetTypes} from '#/main/core/widget/prop-types'

const WidgetPreview = props =>
  <a className="widget-preview" role="button" onClick={props.onClick}>
    <h5 className="widget-title">
      {trans(props.name, {}, 'widget')}

      {0 !== props.sources.length &&
        <span className="label label-primary">{props.sources.length}</span>
      }
    </h5>
  </a>

implementPropTypes(WidgetPreview, WidgetTypes, {
  onClick: T.func.isRequired
})

const WidgetsGrid = props =>
  <div className="modal-body">
    {props.widgets.map((widget, index) =>
      <WidgetPreview
        key={index}
        onClick={() => props.add(widget)}
        {...widget}
      />
    )}
  </div>

WidgetsGrid.propTypes = {
  widgets: T.arrayOf(T.shape(
    WidgetTypes.propTypes
  )).isRequired,
  add: T.func.isRequired
}

class ContentType extends Component {
  constructor(props) {
    super(props)

    this.state = {
      activeTag: 'all'
    }
  }

  render() {
    return (
      <div>
        <ul className="nav nav-tabs">
          {['all']
            .concat(uniq(flatten(this.props.availableTypes.map(widget => widget.tags))))
            .map(tag =>
              <li key={tag} className={classes({
                active: tag === this.state.activeTag
              })}>
                <a
                  role="button"
                  href=""
                  onClick={(e) => {
                    e.preventDefault()
                    this.setState({activeTag: tag})
                  }}
                >
                  {trans(tag)}
                </a>
              </li>
            )
          }
        </ul>

        <WidgetsGrid
          widgets={'all' === this.state.activeTag ?
            this.props.availableTypes :
            this.props.availableTypes.filter(widget => widget.tags && -1 !== widget.tags.indexOf(this.state.activeTag))
          }
          add={(contentType) => this.props.select(contentType)}
        />
      </div>
    )
  }
}

ContentType.propTypes = {
  availableTypes: T.arrayOf(T.shape(
    WidgetTypes.propTypes
  )).isRequired,
  select: T.func.isRequired
}

export {
  ContentType
}
