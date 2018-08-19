import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classNames from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {hasPermission} from '#/main/core/resource/permissions'
import {currentUser} from '#/main/core/user/current'
import {implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {Heading} from '#/main/core/layout/components/heading'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {Section as SectionTypes} from '#/plugin/wiki/resources/wiki/prop-types'
import {WikiSectionForm} from '#/plugin/wiki/resources/wiki/player/components/wiki-section-form'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {actions} from '#/plugin/wiki/resources/wiki/player/store'
import {MODAL_WIKI_SECTION_DELETE} from '#/plugin/wiki/resources/wiki/player/modals/section'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'

const loggedUser = currentUser()

const WikiSectionContent = props =>
  <div className="wiki-section-content">
    <Heading
      key={`wiki-section-title-${props.section.id}`}
      level={props.num.length + 3}
      className="wiki-section-title"
    >
      {props.section.activeContribution.title &&
      <span className="wiki-section-title-text">
        {props.displaySectionNumbers && props.num && Array.isArray(props.num) && <span className="ordering">{props.num.join('.')}</span>}
        <span className="title">{props.section.activeContribution.title}</span>
      </span>
      }
      {!props.section.meta.visible &&
      <span className="wiki-section-invisible-text">
          ({trans(props.canEdit ? 'invisible' : 'waiting_for_moderation', {}, 'icap_wiki')})
      </span>
      }
      <span className="wiki-section-actions">
        {!props.isRoot &&
        <Button
          type={CALLBACK_BUTTON}
          callback={() => {
            alert('CALLBACK')
            document.getElementsByClassName('page-title')[0].scrollIntoView({block: 'end', behavior: 'smooth',  inline: 'start'})}
          }
          icon={'fa fa-fw fa-arrow-up'}
          className={'btn btn-link'}
          tooltip="top"
          label={trans('back_to_top', {}, 'icap_wiki')}
          title={trans('back_to_top', {}, 'icap_wiki')}
        />
        }
        {props.loggedUserId !== null && (props.canEdit || props.mode !== '2') &&
        <span>
          <Button
            id={`wiki-section-add-${props.section.id}`}
            type={CALLBACK_BUTTON}
            icon={'fa fa-fw fa-plus'}
            className={classNames({'btn': !props.isRoot}, {'btn-link': !props.isRoot}, {'page-actions-btn': props.isRoot})}
            tooltip="top"
            primary={props.isRoot}
            callback={() => {
              props.addSection(props.section.id)
            }}
            label={trans(props.isRoot ? 'create_new_section' : 'add_new_subsection', {}, 'icap_wiki')}
            title={trans(props.isRoot ? 'create_new_section' : 'add_new_subsection', {}, 'icap_wiki')}
            confirm={!props.saveEnabled ? undefined : {
              message: trans('unsaved_changes_warning'),
              button: trans('proceed')
            }}
          />
          <Button
            id={`wiki-section-edit-${props.section.id}`}
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-pencil"
            className="btn btn-link"
            tooltip="top"
            callback={() => props.editSection(props.section)}
            label={trans('edit', {}, 'icap_wiki')}
            title={trans('edit', {}, 'icap_wiki')}
            confirm={!props.saveEnabled ? undefined : {
              message: trans('unsaved_changes_warning'),
              button: trans('proceed')
            }}
          />
          <Button
            id={`wiki-section-history-${props.section.id}`}
            type={LINK_BUTTON}
            icon="fa fa-fw fa-history"
            className="btn btn-link"
            tooltip="top"
            target={`/history/${props.section.id}`}
            label={trans('history', {}, 'icap_wiki')}
            title={trans('history', {}, 'icap_wiki')}
          />
          {props.loggedUserId !== null && props.canEdit && props.setSectionVisibility && !props.isRoot &&
          <Button
            id={`wiki-section-toggle-visibility-${props.section.id}`}
            type={CALLBACK_BUTTON}
            icon={props.section.meta.visible ? 'fa fa-fw fa-eye' : 'fa fa-fw fa-eye-slash'}
            className="btn btn-link"
            tooltip="top"
            label={trans(props.section.meta.visible ? 'render_invisible' : 'render_visible', {}, 'icap_wiki')}
            title={trans(props.section.meta.visible ? 'render_invisible' : 'render_visible', {}, 'icap_wiki')}
            callback={() => props.setSectionVisibility(props.section.id, !props.section.meta.visible)}
          />
          }
          {!props.isRoot && props.loggedUserId !== null && (props.canEdit || (props.section.meta.creator && props.loggedUserId === props.section.meta.creator.id)) &&
          <Button
            id={`wiki-section-delete-${props.section.id}`}
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash-o"
            className="btn btn-link"
            dangerous={true}
            tooltip="top"
            label={trans('delete')}
            title={trans('delete')}
            callback={() => props.deleteSection(props.wikiId, props.section)}
          />
          }
        </span>
        }
      </span>
    </Heading>
    {props.section.activeContribution.text && <HtmlText className="wiki-section-text">{props.section.activeContribution.text}</HtmlText>}
  </div>

implementPropTypes(WikiSectionContent, SectionTypes, {
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
        className={classNames(
          'wiki-section',
          {'wiki-section-root': this.isRoot()},
          {'wiki-section-invisible': !this.props.section.meta.visible}
        )}
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
    displaySectionNumbers: props.displaySectionNumbers ? props.displaySectionNumbers : selectors.wiki(state).display.sectionNumbers,
    mode: selectors.mode(state),
    wikiId: selectors.wiki(state).id,
    currentSection: selectors.sections(state).currentSection,
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    loggedUserId: loggedUser === null ? null : loggedUser.id,
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
