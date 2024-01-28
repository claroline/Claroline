import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import get from 'lodash/get'
import times from 'lodash/times'
import sum from 'lodash/sum'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/widget/editor/modals/parameters/store'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'saveEnabled', 'save', 'widget', 'loadWidget', 'formData')}
    className="home-section-parameters"
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={props.widget.name}
    onEntering={() => props.loadWidget(props.widget)}
    size="lg"
  >
    <FormData
      className="widget-section-form"
      level={5}
      flush={true}
      name={selectors.STORE_NAME}
      sections={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'display.layout',
              type: 'string',
              label: trans('widget_layout'),
              hideLabel: true,
              render: (widget) => {
                const layout = get(widget, 'display.layout') || [1]

                const LayoutPreview =
                  <div className="widget-layout-preview">
                    <div className="row">
                      {times(layout.length, col =>
                        <div key={col} className={`widget-col col-md-${(12 / sum(layout)) * layout[col]}`}>
                          <div className="widget-col-preview"></div>
                        </div>
                      )}
                    </div>
                  </div>

                return LayoutPreview
              }
            }, {
              name: 'visible',
              type: 'boolean',
              displayed: false,
              label: trans('publish_section', {}, 'widget')
            }, {
              name: 'display.textColor',
              type: 'color',
              label: trans('textColor'),
              options: {colorIcon: 'fa fa-fw fa-font'}
            }
          ]
        }, {
          id: 'title',
          icon: 'fa fa-fw fa-heading',
          title: trans('title'),
          subtitle: trans('Ajouter un titre à la section et configurer son affichage'),
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('title')
            }, {
              name: 'description',
              type: 'string',
              label: trans('description'),
              options: {long: true, minRows: 1},
              linked: [
                {
                  name: 'display.titleLevel',
                  type: 'choice',
                  label: trans('titleLevel'),
                  required: true,
                  options: {
                    noEmpty: true,
                    condensed: false,
                    inline: true,
                    choices: {1: 1, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6}
                  }
                }, {
                  name: 'display.alignName',
                  label: trans('title_align'),
                  type: 'choice',
                  required: true,
                  options: {
                    noEmpty: true,
                    condensed: false,
                    inline: true,
                    choices: {
                      left: trans('align_left'),
                      center: trans('align_center'),
                      right: trans('align_right')
                    }
                  }
                }, {
                  name: 'display.titleColor',
                  label: trans('color'),
                  type: 'color',
                  options: {colorIcon: 'fa fa-fw fa-font'}
                }
              ]
            }
          ]
        }, {
          id: 'box',
          icon: 'fa fa-fw fa-square',
          title: trans('Boîte'),
          fields: [
            {
              name: 'display.borderColor',
              label: trans('border'),
              type: 'color'
            }, {
              name: 'display.boxShadow',
              type: 'string',
              label: trans('boxShadow')
            }, {
              name: 'display.borderRadius',
              type: 'string',
              label: trans('borderRadius')
            }, {
              name: 'display.backgroundUrl',
              label: trans('backgroundImage'),
              type: 'image'
            }, {
              name: 'display.backgroundColor',
              label: trans('backgroundColor'),
              type: 'color'
            }
          ]
        }, {
          id: 'sizing',
          icon: 'fa fa-fw fa-ruler-combined',
          title: trans('Taille'),
          subtitle: trans('Configurer la taille d\'affichage de la section'),
          fields: [
            {
              name: 'display.maxContentWidth',
              type: 'string',
              label: trans('maxContentWidth')
            }, {
              name: 'display.minHeight',
              type: 'string',
              label: trans('minHeight')
            }
          ]
        }
      ]}
    >
      <Button
        className="modal-btn"
        variant="btn"
        size="lg"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        htmlType="submit"
        callback={() => {
          props.save(props.formData)
          props.fadeModal()
        }}
      />
    </FormData>
  </Modal>

ParametersModal.propTypes = {
  widget: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  formData: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  loadWidget: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
