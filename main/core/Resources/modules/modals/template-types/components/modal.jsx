import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/app/intl/translation'
import {selectors} from '#/main/core/modals/template-types/store'
import {TemplateType as TemplateTypeType} from '#/main/core/administration/template/prop-types'
import {TemplateTypeCard} from '#/main/core/administration/template/data/components/template-type-card'

const TemplateTypesModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'confirmText', 'selected', 'selectAction', 'reset')}
      className="data-picker-modal"
      icon="fa fa-fw fa-file-alt"
      bsSize="lg"
      onExiting={props.reset}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_template_type_list'],
          autoload: true
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
            primary: true
          }, {
            name: 'description',
            type: 'string',
            label: trans('description'),
            displayed: true,
            filterable: false,
            sortable: false,
            calculated: (rowData) => trans(`${rowData.name}_desc`, {}, 'template')
          }
        ]}
        card={TemplateTypeCard}
      />

      <Button
        label={props.confirmText}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

TemplateTypesModal.propTypes = {
  title: T.string,
  confirmText: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(TemplateTypeType.propTypes)).isRequired,
  reset: T.func.isRequired
}

TemplateTypesModal.defaultProps = {
  title: trans('template_types'),
  confirmText: trans('select', {}, 'actions')
}

export {
  TemplateTypesModal
}
