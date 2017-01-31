# ExoBundle

A bundle that permits to create questions and organize it into quizzes.

## Supported question types

The supported question types are referenced in `UJM\ExoBundle\Library\Question\QuestionType`.

[Show QuestionType.php](Library/Question/QuestionType.php)

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
- `validateAfterSchema($data, array $options = [])` : adds some custom validation that can not be achieved by JSON Schema.

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

Add your new type to [QuestionType.php](Library/Question/QuestionType.php).

### Create the data model

### Create the JSON Schema

In order to use validators, it is needed to add a schema for the new Question in [JSON Quiz](https://github.com/json-quiz/json-quiz).

A new Question type needs 2 schemas : 
- One to define the Question data format
- One to define the Answer data format

### Create the Serializer

The question type serializer must be tagged in order to be collected by the `QuestionSerializerCollector`
during compiler pass.

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

The `QuestionSerializer` is now able to forward the serialization to the correct question type serializer based 
on the question type.

### Create the Validator
