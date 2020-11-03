import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {toKey} from '#/main/core/scaffolding/text'
import {ContentMeta} from '#/main/app/content/components/meta'
import {ContentTitle} from '#/main/app/content/components/title'
import {Sections, Section} from '#/main/app/content/components/sections'

import {DataDetailsSection as DataDetailsSectionTypes} from '#/main/app/content/details/prop-types'
import {createDetailsDefinition} from '#/main/app/content/details/utils'
import {DetailsProp} from '#/main/app/content/details/components/prop'

// TODO : there are big c/c from Form component but I don't know if we can do better
// TODO : manage linked fields

const DetailsData = props => {
  const hLevel = props.level + (props.title ? 1 : 0)
  let hDisplay
  if (props.displayLevel) {
    hDisplay = props.displayLevel + (props.title ? 1 : 0)
  }

  const sections = createDetailsDefinition(props.sections)

  const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
  const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
  const openedSection = otherSections.find(section => section.defaultOpened)

  return (
    <div className={classes('data-details', props.className)}>
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
        <div key={toKey(primarySection.title)} className="panel panel-default primary-section">
          <div className="panel-body">
            <ContentTitle
              level={hLevel}
              displayed={false}
              title={primarySection.title}
            />

            {primarySection.fields.map(field =>
              <DetailsProp
                {...field}
                key={field.name}
                data={props.data}
              />
            )}

            {primarySection.component}
            {!primarySection.component && primarySection.render && primarySection.render()}
          </div>
        </div>
      )}

      {0 !== otherSections.length &&
        <Sections
          level={hLevel}
          displayLevel={hDisplay}
          defaultOpened={openedSection ? openedSection.id : undefined}
        >
          {otherSections.map(section =>
            <Section
              key={toKey(section.title)}
              icon={section.icon}
              title={section.title}
              className={section.className}
            >
              {section.fields.map(field =>
                <DetailsProp
                  {...field}
                  key={field.name}
                  data={props.data}
                />
              )}

              {section.component}
              {!section.component && section.render && section.render()}
            </Section>
          )}
        </Sections>
      }

      {props.children}
    </div>
  )
}

DetailsData.propTypes = {
  className: T.string,
  level: T.number,
  displayLevel: T.number,
  title: T.string,
  data: T.object,
  meta: T.bool,
  sections: T.arrayOf(T.shape(
    DataDetailsSectionTypes.propTypes
  )).isRequired,
  children: T.node
}

DetailsData.defaultProps = {
  level: 2,
  data: {},
  meta: false
}

export {
  DetailsData
}
