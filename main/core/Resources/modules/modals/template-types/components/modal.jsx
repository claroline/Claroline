import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/app/intl/translation'
import {selectors} from '#/main/core/modals/template-types/store'
import {TemplateTypeList} from '#/main/core/administration/template/components/template-type-list'
import {TemplateType as TemplateTypeType} from '#/main/core/administration/template/prop-types'

const TemplateTypesPickerModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'confirmText', 'selected', 'selectAction', 'resetSelect')}
      className="template-types-picker-modal"
      icon="fa fa-fw fa-file-alt"
      bsSize="lg"
      onExiting={() => props.resetSelect()}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_template_type_list'],
          autoload: true
        }}
        definition={TemplateTypeList.definition}
        card={TemplateTypeList.card}
        display={props.display}
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

TemplateTypesPickerModal.propTypes = {
  title: T.string,
  confirmText: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(TemplateTypeType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

TemplateTypesPickerModal.defaultProps = {
  title: trans('template_type_selector'),
  confirmText: trans('select', {}, 'actions')
}

export {
  TemplateTypesPickerModal
}
