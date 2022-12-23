import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {ProfileFacet as ProfileFacetTypes} from '#/main/community/profile/prop-types'
import {selectors} from '#/main/community/tools/community/profile/store'

const FacetSection = props =>
  <FormSection
    {...omit(props, ['parentIndex', 'index', 'remove'])}
    title={props.title || trans('profile_facet_section')}
    className="embedded-form-section"
    actions={[
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash-o',
        label: trans('delete'),
        callback: props.remove,
        dangerous: true,
        confirm: {
          title: trans('profile_remove_section'),
          message: trans('profile_remove_section_question')
        }
      }
    ]}
  >
    <FormData
      embedded={true}
      level={3}
      name={selectors.FORM_NAME}
      dataPart={`[${props.parentIndex}].sections[${props.index}]`}
      sections={[
        {
          icon: 'fa fa-fw fa-cog',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'title',
              type: 'string',
              label: trans('title'),
              required: true
            }, {
              name: 'fields',
              type: 'fields',
              label: trans('fields_list'),
              required: true,
              options: {
                placeholder: trans('profile_section_no_field'),
                fields: props.fields,
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
  fields: T.array,
  remove: T.func.isRequired
}

const ProfileFacet = props =>
  <FormData
    level={2}
    name={selectors.FORM_NAME}
    dataPart={`[${props.index}]`}
    buttons={true}
    target={['apiv2_profile_configure']}
    sections={[
      {
        icon: 'fa fa-fw fa-cog',
        title: trans('parameters'),
        fields: [
          {
            name: 'title',
            type: 'string',
            label: trans('title'),
            required: true
          }, {
            name: 'display.creation',
            type: 'boolean',
            label: trans('display_on_create'),
            displayed: !props.facet.meta.main
          }
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
            fields={props.fields}
            remove={() => props.removeSection(props.facet.id, section.id)}
          />
        )}
      </FormSections>
    }

    {0 === props.facet.sections.length &&
      <ContentPlaceholder
        size="lg"
        icon="fa fa-frown-o"
        title={trans('profile_facet_no_section')}
      />
    }

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-block btn-emphasis"
      label={trans('profile_facet_section_add')}
      callback={() => props.addSection(props.facet.id)}
      primary={true}
    />
  </FormData>

ProfileFacet.propTypes = {
  index: T.number.isRequired,
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired,
  fields: T.array,
  addSection: T.func.isRequired,
  removeSection: T.func.isRequired
}

ProfileFacet.defaultProps = {
  facet: ProfileFacetTypes.defaultProps
}

export {
  ProfileFacet
}
