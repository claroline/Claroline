import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import flatten from 'lodash/flatten'
import uniq from 'lodash/uniq'

import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {Widget as WidgetTypes} from '#/main/core/widget/prop-types'

const MODAL_ADD_WIDGET = 'MODAL_ADD_WIDGET'

const WidgetPreview = props =>
  <a className="widget-preview" role="button" onClick={props.onClick}>
    <h5 className="widget-title">
      {trans(props.name, {}, 'widget')}
    </h5>

    {props.meta.abstract}
  </a>

WidgetPreview.propTypes = {
  onClick: T.func.isRequired
}

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

class AddWidgetModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      activeTag: 'all',
      tags: [].concat(['all'], uniq(flatten(this.props.availableWidgets.map(widget => widget.tags)))),
      widgets: this.props.availableWidgets
    }

    this.filterTypes = this.filterTypes.bind(this)
  }

  filterTypes(tag) {
    this.setState({
      activeTag: tag,
      widgets: 'all' === tag ?
        this.props.availableWidgets :
        this.props.availableWidgets.filter(widget => widget.tags && -1 !== widget.tags.indexOf(tag))
    })
  }

  render() {
    return (
      <Modal
        {...this.props}
        icon="fa fa-fw fa-plus"
        title={trans('add_widget', {}, 'widget')}
        bsSize="lg"
      >
        <ul className="nav nav-tabs">
          {this.state.tags.map((tag, index) =>
            <li key={index} className={classes({
              active: tag === this.state.activeTag})
            }>
              <a
                role="button"
                href=""
                onClick={(e) => {
                  e.preventDefault()
                  this.filterTypes(tag)
                }}
              >
                {trans(tag, {}, 'widget')}
              </a>
            </li>
          )}
        </ul>

        <WidgetsGrid
          widgets={this.state.widgets}
          add={this.props.add}
        />
      </Modal>
    )
  }
}

AddWidgetModal.propTypes = {
  availableWidgets: T.arrayOf(T.shape(
    WidgetTypes.propTypes
  )).isRequired,
  add: T.func.isRequired
}

export {
  MODAL_ADD_WIDGET,
  AddWidgetModal
}
