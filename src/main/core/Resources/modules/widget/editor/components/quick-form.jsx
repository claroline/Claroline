import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'
import set from 'lodash/set'

import {trans} from '#/main/app/intl'
import {Offcanvas} from '#/main/app/overlays/offcanvas'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'
import {FormFieldset} from '#/main/app/content/form/components/fieldset'
import {COLOR_BUTTON} from '#/main/theme/buttons'
import {LevelSelector} from '#/plugin/home/components/level-selector'
import {AlignmentSelector} from '#/plugin/home/components/alignment-selector'
import {DataInput} from '#/main/app/data/components/input'
import {WidgetContentIcon} from '#/main/core/widget/content/components/icon'

const QuickFormSection = (props) => {
  if (!props.enabled) {
    return (
      <Button
        className="quick-editor-section quick-editor-title text-start"
        type={CALLBACK_BUTTON}
        icon="fa fa-plus"
        label={props.title}
        callback={props.enable}
      />
    )
  }

  return (
    <div className="quick-editor-section">
      <h5 className="quick-editor-title d-flex align-items-center">
        {props.title}

        {props.disable &&
          <Button
            className="ms-auto me-n2 btn btn-text-secondary"
            type={CALLBACK_BUTTON}
            size="sm"
            icon="fa fa-fw fa-trash"
            label={trans('delete', {}, 'actions')}
            callback={props.disable}
            tooltip="bottom"
          />
        }
      </h5>

      {props.children}
    </div>
  )
}

QuickFormSection.propTypes = {
  title: T.string.isRequired,
  enabled: T.bool.isRequired,
  children: T.any,
  disable: T.func,
  enable: T.func
}

const TitleBlock = (props) =>
  <QuickFormSection
    title="Titre de la section"
    enabled={props.enabled}
    enable={props.enable}
    disable={props.disable}
  >
    <div className="d-flex gap-1 mb-2">
      <Button
        id="title-color"
        className="rounded-1"
        type={COLOR_BUTTON}
        size="sm"
        icon="fa fa-fw fa-heading"
        label={trans('Couleur du texte')}
        color={props.color}
        onSelect={(color) => props.updateProp('display.titleColor', color)}
        tooltip="bottom"
      />

      <AlignmentSelector
        id="title-level"
        label={trans('Alignement')}
        value={props.align}
        onChange={(align) => props.updateProp('display.alignName', align)}
      />

      <LevelSelector
        id="title-level"
        className="flex-fill"
        label={trans('Taille d\'affichage')}
        value={props.level}
        onChange={(level) => props.updateProp('display.titleLevel', level)}
      />
    </div>

    <DataInput
      id="add-subtitle"
      type="boolean"
      label={trans('Ajouter un sous-titre')}
      onChange={() => true}
      required={true}
    />
  </QuickFormSection>

TitleBlock.propTypes = {
  title: T.string,
  level: T.number,
  align: T.oneOf(['start', 'center', 'end']),
  color: T.string,
  background: T.string,
  updateProp: T.func.isRequired,
  disable: T.func
}

const DescriptionBlock = (props) =>
  <QuickFormSection
    title="Description de la section"
    enabled={props.enabled}
    enable={props.enable}
    disable={props.disable}
  />

const BackgroundBlock = (props) =>
  <QuickFormSection
    title="Arrière-plan de la section"
    enabled={props.enabled}
    enable={props.enable}
    disable={props.disable}
  >
    <FormFieldset
      id="quick-form-bg"
      size="sm"
      data={{
        backgroundUrl: props.backgroundUrl,
        backgroundColor: props.backgroundColor
      }}
      fields={[
        {
          name: 'backgroundColor',
          type: 'color',
          label: trans('color'),
          hideLabel: true,
          options: {
            colorIcon: 'fa fa-fw fa-fill'
          }
        }, {
          name: 'backgroundUrl',
          type: 'image',
          label: trans('background'),
          hideLabel: true,
          linked: [

          ]
        },
      ]}
      updateProp={(propName, propValue) => props.updateProp('display.'+ propName, propValue)}
      setErrors={() => true}
    />

    {props.backgroundUrl &&
      <div className="row">
        <DataInput
          className="col-6"
          id="background-format"
          type="choice"
          label={trans('Format')}
          onChange={() => true}
          required={true}
          size="sm"
          options={{
            condensed: true,
            noEmpty: true,
            choices: {
              'original': "Taille originale",
              'cover': "Etirer",
              'repeat': "Mosaïque"
            }
          }}
        />

        <DataInput
          className="col-6"
          id="background-position"
          type="choice"
          label={trans('Position')}
          onChange={() => true}
          required={true}
          size="sm"
          options={{
            condensed: true,
            noEmpty: true,
            choices: {
              'center': "Centrer"
            }
          }}
        />
      </div>
    }
  </QuickFormSection>

BackgroundBlock.propTypes = {
  backgroundColor: T.string,
  backgroundUrl: T.string,
  updateProp: T.func.isRequired,
  disable: T.func
}

const WidgetQuickForm = (props) => {
  const updateProp = (propName, propValue) => {
    const updatedContent = cloneDeep(props.editedContent)
    set(updatedContent, propName, propValue)

    props.update(updatedContent)
  }

  const [enableTitle, setEnableTitle] = useState(!isEmpty(props.editedContent.title))
  const [enableDescription, setEnableDescription] = useState(!isEmpty(props.editedContent.description))
  const [enableBackground, setEnableBackground] = useState(!isEmpty(get(props.editedContent, 'display.backgroundUrl') || !isEmpty(get(props.editedContent, 'display.backgroundColor'))))

  return (
    <Offcanvas className="quick-editor" placement="end" show={props.show} onHide={props.close} scroll={true} backdrop={false}>
      <Offcanvas.Header closeButton={true}>
        <Offcanvas.Title>
          {trans('Modification rapide')}
        </Offcanvas.Title>
      </Offcanvas.Header>

      <Offcanvas.Body>
        <nav className="quick-editor-nav d-flex gap-1">
          <Button
            className="quick-editor-nav-item active me-auto"
            type={CALLBACK_BUTTON}
            icon="fa fa-cog"
            label={trans('parameters')}
            callback={() => true}
            tooltip="bottom"
          />

          {props.editedContent.contents && props.editedContent.contents.map(content => content ?
            <Button
              className="quick-editor-nav-item"
              type={CALLBACK_BUTTON}
              icon={<WidgetContentIcon className="action-icon" type={content.type} />}
              label={trans(content.type, {}, 'widget')}
              callback={() => true}
              tooltip="bottom"
            /> :
            <Button
              className="quick-editor-nav-item"
              type={CALLBACK_BUTTON}
              icon="fa fa-ban"
              label={trans('Widget vide')}
              callback={() => true}
              tooltip="bottom"
            />
          )}
        </nav>

        {false &&
          <div className="quick-editor-section d-flex gap-1">
            <Button
              className="rounded-1"
              type={COLOR_BUTTON}
              size="sm"
              icon="fa fa-fw fa-paragraph"
              label={trans('Couleur du texte')}
              color={props.color}
              onSelect={(color) => props.updateProp('display.titleColor', color)}
              tooltip="bottom"
            />

            <AlignmentSelector
              id="level"
              label={trans('Alignement')}
              value={props.align}
              onChange={(align) => props.updateProp('display.alignName', align)}
            />
          </div>
        }

        <TitleBlock
          title={props.editedContent.title}

          level={props.editedContent.display.titleLevel}
          color={props.editedContent.display.titleColor}
          align={props.editedContent.display.alignName}
          updateProp={updateProp}

          enabled={enableTitle}
          enable={() => setEnableTitle(true)}
          disable={() => {
            // reset title related data
            const updatedContent = cloneDeep(props.editedContent)
            set(updatedContent, 'title', null)
            set(updatedContent, 'description', null)
            set(updatedContent, 'display.titleLevel', null)
            set(updatedContent, 'display.titleColor', null)
            set(updatedContent, 'display.alignName', null)

            props.update(updatedContent)

            // remove section from form
            setEnableTitle(false)
          }}
        />

        <DescriptionBlock
          updateProp={updateProp}
          enabled={enableDescription}
          enable={() => setEnableDescription(true)}
          disable={() => {
            // reset description related data
            const updatedContent = cloneDeep(props.editedContent)
            set(updatedContent, 'description', null)

            props.update(updatedContent)

            // remove section from form
            setEnableDescription(false)
          }}
        />

        <BackgroundBlock
          backgroundUrl={get(props.editedContent, 'display.backgroundUrl')}
          backgroundColor={get(props.editedContent, 'display.backgroundColor')}
          updateProp={updateProp}
          enabled={enableBackground}
          enable={() => setEnableBackground(true)}
          disable={() => {
            // reset background related data
            const updatedContent = cloneDeep(props.editedContent)
            set(updatedContent, 'display.backgroundUrl', null)
            set(updatedContent, 'display.backgroundColor', null)

            props.update(updatedContent)

            // remove section from form
            setEnableBackground(false)
          }}
        />

        {props.children}
      </Offcanvas.Body>
    </Offcanvas>
  )
}

WidgetQuickForm.propTypes = {
  editedContent: T.shape({
    title: T.string,
    subtitle: T.string,
    description: T.string,

    display: T.shape({
      backgroundUrl: T.string,
      backgroundColor: T.string
    }),
    contents: T.array
  }),
  children: T.node,
  update: T.func.isRequired
}

export {
  WidgetQuickForm
}
