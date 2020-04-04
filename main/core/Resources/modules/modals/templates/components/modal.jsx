import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/modals/templates/store'
import {Template as TemplateTypes} from '#/main/core/data/types/template/prop-types'
import {TemplateCard} from '#/main/core/data/types/template/components/card'

const TemplatesModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'currentLocale', 'selected', 'selectAction', 'reset')}
      className="data-picker-modal"
      icon="fa fa-fw fa-file-alt"
      bsSize="lg"
      onExiting={props.reset}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_lang_template_list', {lang: props.currentLocale}],
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
          }
        ]}
        card={TemplateCard}
      />

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

TemplatesModal.propTypes = {
  title: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  currentLocale: T.string.isRequired,
  selected: T.arrayOf(T.shape(
    TemplateTypes.propTypes
  )).isRequired,
  reset: T.func.isRequired
}

TemplatesModal.defaultProps = {
  title: trans('templates', {}, 'template')
}

export {
  TemplatesModal
}
