# Mida PHP

Mida is a PHP library for interacting with the Mida API, allowing you to perform experiments and track events in your projects.

## Installation

To use Mida in your PHP project, you can install it using Composer. Simply add the following line to your `composer.json` file and run the `composer install` command:

```json
"repositories": [
{
    "type": "package",
    "package": {
        "name": "mida-php",
        "version": "1.0",
        "source": {
            "url": "https://github.com/mida-so/mida-php.git",
            "type": "git",
            "reference": "master"
        }
    }
}],
"require" : {
    "guzzlehttp/guzzle": "^7.0"
}
```

## Usage

To use the Mida class in your project, follow these steps:

1. Include the Mida class in your PHP file:

    ```php
    require_once 'Mida.php';
    ```

2. Create an instance of the Mida class by passing your Mida project key and optional configuration options:

    ```php
    $publicKey = "your_mida_project_key";
    $options = []; // Optional configuration options
    $mida = new Mida($publicKey, $options);
    ```

3. Perform experiments by calling the `getExperiment` method and passing the experiment key and user distinct ID:

    ```php
    $experimentKey = "your_experiment_key";
    $distinctId = "user_distinct_id";

    try {
        $version = $mida->getExperiment($experimentKey, $distinctId);
        if ($version) {
            echo "Experiment version: $version";
        } else {
            echo "No version available for this experiment";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    ```

4. Track events by calling the `setEvent` method and passing the event name and user distinct ID:

    ```php
    $eventName = "your_event_name";
    $distinctId = "user_distinct_id";
    
    try {
        $mida->setEvent($eventName, $distinctId);
        echo "Event tracked successfully";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    ```

## Contributing

Contributions to Mida are welcome! If you encounter any bugs or have suggestions for improvement, please open an issue or submit a pull request.

## License

This library is released under the [MIT License](https://opensource.org/licenses/MIT).

## Contact

For any inquiries or support requests, please contact our team at hello@mida.so.