import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {toKey} from '#/main/core/scaffolding/text'
import {ContentMeta} from '#/main/app/content/components/meta'
import {ContentTitle} from '#/main/app/content/components/title'
import {Sections, Section} from '#/main/app/content/components/sections'

import {DataDetailsSection as DataDetailsSectionTypes} from '#/main/app/content/details/prop-types'
import {createDetailsDefinition} from '#/main/app/content/details/utils'
import {DetailsFieldset} from '#/main/app/content/details/components/fieldset'

function getSectionId(section, formId = null) {
  let id = formId ? `${formId}-` : ''

  id += section.id || toKey(section.title)

  return id
}

const DetailsData = props => {
  const hLevel = props.level + (props.title ? 1 : 0)
  let hDisplay
  if (props.displayLevel) {
    hDisplay = props.displayLevel + (props.title ? 1 : 0)
  }

  const sections = createDetailsDefinition(props.definition || props.sections, props.data)

  const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
  const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
  const openedSection = otherSections.find(section => section.defaultOpened)

  return (
    <div className={classes('data-details', props.className, props.flush && 'data-details-flush', !props.flush && 'content-md')}>
      {props.title &&
        <ContentTitle
          level={props.level}
          displayLevel={props.displayLevel}
          title={props.title}
        />
      }

      {props.meta &&
        <ContentMeta
          creator={get(props.data, 'meta.creator')}
          created={get(props.data, 'meta.created')}
          updated={get(props.data, 'meta.updated')}
        />
      }

      {primarySections.map(primarySection =>
        <section key={toKey(primarySection.title)} className={classes('details-primary-section', !props.flush && 'mb-3')}>
          <ContentTitle
            level={hLevel}
            displayed={false}
            title={primarySection.title}
          />

          <DetailsFieldset
            id={getSectionId(primarySection, props.id)}
            fields={primarySection.fields}
            data={props.data}
            errors={props.errors}
            help={primarySection.help}
          >
            {primarySection.component && createElement(primarySection.component)}
            {!primarySection.component && primarySection.render && primarySection.render()}
          </DetailsFieldset>
        </section>
      )}

      {props.affix}

      {0 !== otherSections.length &&
        <Sections
          level={hLevel}
          displayLevel={hDisplay}
          defaultOpened={openedSection ? openedSection.id : undefined}
          flush={props.flush}
          className={classes(!props.flush && 'mb-3')}
        >
          {otherSections.map(section =>
            <Section
              key={toKey(section.title)}
              icon={section.icon}
              title={section.title}
              className={section.className}
              fill={section.fill}
            >
              <DetailsFieldset
                id={getSectionId(section, props.id)}
                fields={section.fields}
                data={props.data}
                errors={props.errors}
                help={section.help}
              >
                {section.component && createElement(section.component)}
                {!section.component && section.render && section.render()}
              </DetailsFieldset>
            </Section>
          )}
        </Sections>
      }

      {props.children}
    </div>
  )
}

DetailsData.propTypes = {
  id: T.string,
  className: T.string,
  level: T.number,
  displayLevel: T.number,
  title: T.string,
  data: T.object,
  errors: T.object,
  meta: T.bool,
  flush: T.bool,
  /**
   * @deprecated use definition instead
   */
  sections: T.arrayOf(T.shape(
    DataDetailsSectionTypes.propTypes
  )),
  definition: T.arrayOf(T.shape(
    DataDetailsSectionTypes.propTypes
  )).isRequired,
  affix: T.node,
  children: T.node
}

DetailsData.defaultProps = {
  level: 2,
  data: {},
  meta: false,
  flush: false
}

export {
  DetailsData
}
