import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classNames from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {Heading} from '#/main/core/layout/components/heading'
import {ContentHtml} from '#/main/app/content/components/html'

import {Section as SectionTypes} from '#/plugin/wiki/resources/wiki/prop-types'
import {actions} from '#/plugin/wiki/resources/wiki/player/store'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'
import {MODAL_WIKI_SECTION_DELETE} from '#/plugin/wiki/resources/wiki/player/modals/section'
import {WikiSectionForm} from '#/plugin/wiki/resources/wiki/player/components/wiki-section-form'

const WikiSectionContent = props =>
  <section className="wiki-section-content">
    <Heading
      level={props.num.length + 1}
      className="wiki-section-title"
    >
      {props.section.activeContribution.title && props.displaySectionNumbers &&
        <span className="h-numbering">{props.num.join('.')}</span>
      }

      {props.section.activeContribution.title}

      {!props.section.meta.visible &&
        <small className="wiki-section-invisible-text">
          ({trans(props.canEdit ? 'invisible' : 'waiting_for_moderation', {}, 'icap_wiki')})
        </small>
      }

      {props.loggedUserId !== null && (props.canEdit || props.mode !== '2') &&
        <Toolbar
          id={`actions-${props.section.id}`}
          className="wiki-section-actions"
          buttonName="btn btn-link"
          tooltip="left"
          toolbar="more"
          actions={[
            {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans(props.isRoot ? 'create_new_section' : 'add_new_subsection', {}, 'icap_wiki'),
              callback: () => props.addSection(props.section.id),
              confirm: !props.saveEnabled ? undefined : {
                message: trans('unsaved_changes_warning'),
                button: trans('proceed')
              }
            }, {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-pencil',
              label: trans('edit', {}, 'actions'),
              callback: () => props.editSection(props.section),
              confirm: !props.saveEnabled ? undefined : {
                message: trans('unsaved_changes_warning'),
                button: trans('proceed')
              }
            }, {
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-history',
              label: trans('history', {}, 'icap_wiki'),
              target: `${props.path}/history/${props.section.id}`
            }, {
              type: CALLBACK_BUTTON,
              icon: props.section.meta.visible ? 'fa fa-fw fa-eye' : 'fa fa-fw fa-eye-slash',
              label: trans(props.section.meta.visible ? 'render_invisible' : 'render_visible', {}, 'icap_wiki'),
              callback: () => props.setSectionVisibility(props.section.id, !props.section.meta.visible),
              displayed: props.canEdit && props.setSectionVisibility && !props.isRoot
            }, {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash',
              label: trans('delete', {}, 'actions'),
              callback: () => props.deleteSection(props.wikiId, props.section),
              displayed: !props.isRoot && (props.canEdit || (props.section.meta.creator && props.loggedUserId === props.section.meta.creator.id)),
              dangerous: true
            }
          ]}
        />
      }
    </Heading>

    {props.section.activeContribution.text &&
      <ContentHtml className="wiki-section-text">{props.section.activeContribution.text}</ContentHtml>
    }
  </section>

implementPropTypes(WikiSectionContent, SectionTypes, {
  path: T.string.isRequired,
  isRoot: T.bool.isRequired
})

class WikiSectionComponent extends Component {
  constructor(props) {
    super(props)
  }

  isRoot() {
    return this.props.num.length === 0
  }

  render() {
    return (
      <div
        id={`wiki-section-${this.props.section.id}`}
        className={classNames('wiki-section', {
          'wiki-section-root': this.isRoot(),
          'wiki-section-invisible': !this.props.section.meta.visible
        })}
      >
        {(this.props.currentSection && this.props.currentSection.id && this.props.currentSection.id === this.props.section.id) ?
          <WikiSectionForm
            cancelChanges={this.props.editSection}
            saveChanges={(isNew) => this.props.saveSection(this.props.section.id, isNew)}
          /> :
          <WikiSectionContent
            {...this.props}
            isRoot={this.isRoot()}
          />
        }
        {(this.props.currentSection && this.props.currentSection.parentId && this.props.currentSection.parentId === this.props.section.id) &&
          <WikiSectionForm
            cancelChanges={this.props.addSection}
            saveChanges={(isNew) => this.props.saveSection(this.props.section.id, isNew)}
          />
        }
        {
          !this.isRoot() &&
          this.props.section.children &&
          this.props.section.children.map(
            (section, index) =>
              <WikiSectionComponent
                key={section.id}
                num={this.props.num.concat([index + 1])}
                section={section}
                {...omit(this.props, 'num', 'section')}
              />
          )
        }
      </div>
    )
  }
}

implementPropTypes(WikiSectionComponent, SectionTypes)

const WikiSection = connect(
  (state, props = {}) => ({
    path: resourceSelect.path(state),
    displaySectionNumbers: props.displaySectionNumbers ? props.displaySectionNumbers : selectors.wiki(state).display.sectionNumbers,
    mode: selectors.mode(state),
    wikiId: selectors.wiki(state).id,
    currentSection: selectors.sections(state).currentSection,
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    loggedUserId: securitySelectors.currentUser(state) === null ? null : securitySelectors.currentUser(state).id,
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME + '.sections.currentSection'))
  }),
  (dispatch, props = {}) => (
    {
      setSectionVisibility: props.setSectionVisibility === null ? null : (sectionId, visible) => dispatch(actions.setSectionVisibility(sectionId, visible)),
      addSection: (parentId = null) => dispatch(actions.setCurrentParentSection(parentId)),
      editSection: (section = null) => dispatch(actions.setCurrentEditSection(section)),
      deleteSection: (wikiId, section) => dispatch(
        modalActions.showModal(MODAL_WIKI_SECTION_DELETE, {
          deleteSection: (deleteChildren) => dispatch(actions.deleteSection(wikiId, section.id, deleteChildren)),
          sectionTitle: section.activeContribution.title
        })
      ),
      saveSection: (id, isNew) => dispatch(formActions.saveForm(selectors.STORE_NAME + '.sections.currentSection', [isNew ? 'apiv2_wiki_section_create' : 'apiv2_wiki_section_update', {id}]))
    }
  )
)(WikiSectionComponent)

export {
  WikiSection
}
