import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/modals/templates/store'
import {Template as TemplateTypes} from '#/main/core/data/types/template/prop-types'
import {TemplateCard} from '#/main/core/data/types/template/components/card'

class TemplatesModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      initialized: false
    }
  }

  render() {
    const selectAction = this.props.selectAction(this.props.selected)

    return (
      <Modal
        {...omit(this.props, 'currentLocale', 'selected', 'selectAction', 'reset', 'resetFilters', 'filters')}
        className="data-picker-modal"
        icon="fa fa-fw fa-file-alt"
        bsSize="lg"
        onEnter={() => {
          this.props.resetFilters(this.props.filters)
          this.setState({initialized: true})
        }}
        onExited={this.props.reset}
      >
        <ListData
          name={selectors.STORE_NAME}
          fetch={{
            url: ['apiv2_template_list'],
            autoload: this.state.initialized
          }}
          definition={[
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              displayed: true,
              filterable: false,
              sortable: false,
              calculated: (rowData) => trans(rowData.name, {}, 'template'),
              options: {
                domain: 'template'
              },
              primary: true
            }, {
              name: 'description',
              type: 'string',
              label: trans('description'),
              displayed: true,
              filterable: false,
              sortable: false,
              calculated: (rowData) => trans(`${rowData.name}_desc`, {}, 'template')
            }, {
              name: 'typeName',
              type: 'string',
              label: trans('type'),
              displayable: false,
              filterable: true,
              sortable: false
            }
          ]}
          card={TemplateCard}
        />

        <Button
          label={trans('select', {}, 'actions')}
          {...selectAction}
          className="modal-btn btn"
          primary={true}
          disabled={0 === this.props.selected.length}
          onClick={this.props.fadeModal}
        />
      </Modal>
    )
  }
}

TemplatesModal.propTypes = {
  title: T.string,
  filters: T.arrayOf(T.shape({
    // TODO : list filter types
  })),
  selectAction: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired,

  // from store
  currentLocale: T.string.isRequired,
  selected: T.arrayOf(T.shape(
    TemplateTypes.propTypes
  )).isRequired,
  resetFilters: T.func.isRequired,
  reset: T.func.isRequired
}

TemplatesModal.defaultProps = {
  title: trans('templates', {}, 'template'),
  filters: []
}

export {
  TemplatesModal
}
