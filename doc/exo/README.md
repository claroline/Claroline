# ExoBundle

A bundle that permits to create questions and organize it into quizzes.

## Supported question types

The supported question types are referenced in `/Distribution/plugin/exo/Library/Item/ItemType.php`.

[Show ItemType.php](../../plugin/exo/Library/Item/ItemType.php)

## Validation

The validation system uses JSON Schemas to check data constraints.
The validators are located inside the `Validator` sub-namespace.

A Validator must implement `UJM\ExoBundle\Library\Validator\ValidatorInterface`.
In order to add JSON Schema feature to the validator, it needs to extends `UJM\ExoBundle\Library\Validator\JsonSchemaValidator`.

```php
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class ExerciseValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'quiz/schema.json';
    }

    public function validateAfterSchema($exercise, array $options = []) {}

    // ...
}
```

A JsonSchemaValidator must implements 2 methods :
- `getJsonSchemaUri()` : returns the relative path to the JSON Schema file.
- `validateAfterSchema($data, array $options = [])` : adds some custom
validation that can not be achieved by JSON Schema.

## Serialization

The serializers are located inside the `Serializer` sub-namespace.

A Serializer must implement `UJM\ExoBundle\Library\Serializer\SerializerInterface`.

```php
use UJM\ExoBundle\Library\Serializer\SerializerInterface;

class ExerciseSerializer implements SerializerInterface
{
    // ...
}
```

A Serializer exposes 2 methods :
- `serialize($entity, array $options = [])` : converts `$entity` into raw data (e.g. array, stdClass)
- `deserialize($data, $entity = null, array $options = [])` : converts `$data` into symfony entities

The `$options` parameters can contain a `$options['entity']`. If it is set, the deserialization will populate this entity
instead of creating a new one.

The serialization process can be configured through the `$options` parameters.
This parameter permits to handle custom serialization logic (e.g. enable or disable serialization of solution data).

## [API] Adding a new question type

### Register the new type

Add your new MIME type to [ItemType.php](../../plugin/exoLibrary/Item/ItemType.php).

### Create the JSON Schema

In order to use validators, it is needed to add a schema for the new Question in [JSON Quiz](https://github.com/json-quiz/json-quiz).

A new Question type needs 2 schemas:
- One to define the Question data format in `format/question`
- One to define the Answer data format in `format/answer-data`

E.g. for the simple question type 'open', the Question data format:

```{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "allOf": [
    {
      "$ref": "http://json-quiz.github.io/json-quiz/schemas/question/base/schema.json"
    },
    {
      "type": "object",
      "properties": {
        "type": {
          "enum": ["application/x.open+json"]
        },
        "contentType": {
          "enum": ["text", "audio", "video"],
          "description": "The content type of the answer to submit to this question"
        },
        "maxLength": {
          "type": "number",
          "minimum": 0,
          "default": 0,
          "description": "The maximum length of the answer"
        }
      },
      "required": ["contentType"]
    }
  ]
}
```

The Answer data schema:

```{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "type": "object",
  "properties": {
    "type": {
      "enum": ["application/x.open+json"]
    },
    "data": {
      "type": "string"
    }
  }
}
```
Also create tests in `test` add the question type in:
- `test/answer-data`
- `test/question`

and reference them in [Show assert.js](https://github.com/json-quiz/json-quiz/assert.js).

### Create the data model

Create a question type entity in `/Distribution/plugin/exo/Entity/ItemType/`.

### Create the Serializer

Serializer makes the link between the question type in JSON Schema and the question type entity.

Files are located in `/Distribution/plugin/exo/Serializer/ItemType`.

The question type serializer must be tagged in order to be collected by the
`QuestionSerializerCollector` during compiler pass.

```php
/**
 * @DI\Service("ujm_exo.serializer.question_choice")
 * @DI\Tag("ujm_exo.question.serializer")
 */
class ChoiceSerializer implements QuestionHandlerInterface, SerializerInterface
{
    // ...
}
```

The `ItemSerializer` is now able to forward the serialization to the correct
question type serializer based on the question type.

### Create the Validator

Create validators:
- question type validator in `/Distribution/plugin/exo/Validator/JsonSchema/Item/Type/`
- answer validator in `/Distribution/plugin/exo/Validator/JsonSchema/Attempt/AnswerData`

### Create the Definition

A definition is all necessary references to handle a question type:
- question Validator
- answer Validator
- serializer
- method `correctAnswer(AbstractQuestion $question, $answer)` : correct a given
answer with expected, unexpected and missing answers
- method `expectAnswer(AbstractQuestion $question)` : gives global score

Create a definition for the question type in `/Distribution/plugin/exo/Library/Item/Definition/`.

### Create API unit tests with PHPUnit

To be completed.


## [Font-end] Adding a new question type

Front-end files are located in
`/Distribution/plugin/exo/Resources/modules/items/`.

### Create an item type

Add the new item type in
[Show item-types.js](../../plugin/exo/Resources/modules/items/item-types.js).

### Create views of item

Create or duplicate a folder named by your question type in:
`/Distribution/plugin/exo/Resources/modules/items/`.

You need to create a file named `index.js` which specifies:
- MIME type (same as API)
- question type name (same as folder)
- paper, editor, player and feedback views

### Create view test units with Karma

It is important to test at least each editor.js file for a given question type.

Each reducer actions must be tested in `editor.test.js` file located in the item
front end directory located in:
`Distribution/plugin/exo/Resources/modules/items/`

### Add translations of the new item type

The translation of the item type and its description is needed at least in English and French.

[Show question_types.fr.json](../../plugin/exo/Resources/translations/question_types.fr.json)

### Create an icon for the new item type

A SVG icon is needed for each question type, stored in `icon`.

[See existing icons](https://github.com/claroline/icons/tree/master/inkscape/quiz)

It must respond to some constraints listed in [project README.md](https://github.com/claroline/icons/blob/master/README.md).

Once added to the project, run:
```
npm run build
```

This will optimize SVG file (removes overhead) and generate code to place in:
[Show item-icons.svg](../../plugin/exo/Resources/public/images/item-icons.svg)

## Checklist for a new item type before making a Pull Request

- [ ] Create a MIME type in [ItemType.php](../../plugin/exo/Library/Item/ItemType.php)
- [ ] Create a JSON schema for question and answer and unit tests
- [ ] Reference tests in [assert.js](https://github.com/json-quiz/json-quiz/assert.js)
- [ ] Create the item entity
- [ ] Create a JSON serializer/deserializer
- [ ] Create a question and answer validator
- [ ] Create the item type definition
- [ ] Unit test the API code
- [ ] Reference the new item type in [item-types.js](../../plugin/exo/Resources/modules/items/item-types.js)
- [ ] Create paper, editor, player and feedback views of the new item
- [ ] Unit test views
- [ ] Create at least English and French translations for the new item type and its description
- [ ] Create the item type icon and add SVG code in [item-icons.svg](../../plugin/exo/Resources/public/images/item-icons.svg)
