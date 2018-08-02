import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {t} from '#/main/core/translation'

import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'
import {actions} from '#/main/core/administration/user/profile/actions'
import {select} from '#/main/core/administration/user/profile/selectors'

// todo manage differences between main / default / plugin facets

const FacetSection = props =>
  <FormSection
    {...omit(props, ['parentIndex', 'index', 'remove'])}
    title={props.title || t('profile_facet_section')}
    className="embedded-form-section"
    actions={[
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash-o',
        label: t('delete'),
        callback: props.remove,
        dangerous: true
      }
    ]}
  >
    <FormData
      embedded={true}
      level={3}
      name="profile"
      dataPart={`[${props.parentIndex}].sections[${props.index}]`}
      sections={[
        {
          icon: 'fa fa-fw fa-cog',
          title: t('general'),
          primary: true,
          fields: [
            {
              name: 'title',
              type: 'string',
              label: t('title'),
              required: true
            }, {
              name: 'fields',
              type: 'fields',
              label: t('fields_list'),
              required: true,
              options: {
                placeholder: t('profile_section_no_field'),
                min: 1
              }
            }
          ]
        }
      ]}
    />
  </FormSection>

FacetSection.propTypes = {
  index: T.number.isRequired,
  parentIndex: T.number.isRequired,
  title: T.string,
  remove: T.func.isRequired
}

const ProfileFacetComponent = props =>
  <FormData
    level={2}
    name="profile"
    className="profile-facet"
    dataPart={`[${props.index}]`}
    buttons={true}
    target={['apiv2_profile_update']}
    sections={[
      {
        icon: 'fa fa-fw fa-cog',
        title: t('parameters'),
        fields: [
          {
            name: 'title',
            type: 'string',
            label: t('title'),
            required: true
          }, {
            name: 'display.creation',
            type: 'boolean',
            label: t('display_on_create'),
            displayed: !props.facet.meta.main
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: t('access_restrictions'),
        fields: [
        ]
      }
    ]}
  >
    {0 < props.facet.sections.length &&
      <FormSections level={2}>
        {props.facet.sections.map((section, sectionIndex) =>
          <FacetSection
            id={section.id}
            key={section.id}
            index={sectionIndex}
            parentIndex={props.index}
            title={section.title}
            remove={() => props.removeSection(props.facet.id, section.id)}
          />
        )}
      </FormSections>
    }

    {0 === props.facet.sections.length &&
      <div className="no-section-info">{t('profile_facet_no_section')}</div>
    }

    <div className="text-center">
      <button
        type="button"
        className="btn btn-primary btn-block btn-emphasis"
        onClick={() => props.addSection(props.facet.id)}
      >
        {t('profile_facet_section_add')}
      </button>
    </div>
  </FormData>

ProfileFacetComponent.propTypes = {
  index: T.number.isRequired,
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired,
  addSection: T.func.isRequired,
  removeSection: T.func.isRequired
}

const ProfileFacet = connect(
  state => ({
    index: select.currentFacetIndex(state),
    facet: select.currentFacet(state)
  }),
  dispatch => ({
    addSection(facetId) {
      dispatch(actions.addSection(facetId))
    },
    removeSection(facetId, sectionId) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-trash-o',
          title: t('profile_remove_section'),
          question: t('profile_remove_section_question'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.removeSection(facetId, sectionId))
        })
      )
    }
  })
)(ProfileFacetComponent)

export {
  ProfileFacet
}
