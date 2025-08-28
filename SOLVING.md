# TODO

Hi! :-) This is place for ideas sharing + steps for the refactoring, basically a public todo list. 

## First thoughts

- Well, it is a little bit weird, we have code that is not used anywhere, only in tests - it is okay for some intro challenge, but in real life scenario, we should not have any code that is not used at all - it is dead code and should be deleted.
- I like the way readme.md is written, it can simply run the commands directly from phpstorm, i personally use that too, it is great DX`
- `Sending email to: test@example.com for order: 2` is not really something, developer would like to see when running a tests - it might scare him that it is sending real emails :D
- Running tests again and again, increases the counter of rows - obviously the test suite does not clean after itself. **This might be a bug** and it WILL cause problems and should be fixed.
- This will be fun and enjoyable challenge - at first sight I can see all the prepared pitfalls! No DI (+DIC is missing completely), using hardcoded secrets/credentials, using PDO directly (yeah, mocking PDO might be a lot of fun for someone!). Testing the calls of `error_log()` might be fun too :-)! Missing return types and other typehints (aka strict typing) is just piece of cake.
- Since this is really small "app", solving the challenge with AI would be extremely easy - I will pass this opportunity and train my own skills without AI this time.   
- Generating the order number is definitely issue.
- We will skip Git best practices (branching, merge requests etc) for sake of simplicity and to save time managing branches/pull requests for the challenge purposes.
- The .dockerignore was incomplete + there was typo `test/` vs `tests/`

## The plan

- CI - GH Actions workflow to run what we already have (the tests)
- Install PHPStan - the PHP developer's best friend
- First gotcha! Can't connect to database in CI because of hardcoded hostname in tests - lets fix it first before some refactoring, `vlucas/phpdotenv` is installed already. Náhoda? Nemyslím si!
- Hell no! There is no XDEBUG installed. Probably will add that very soon - developing without is painful!

### Minor issues
- `composer install` should be installed straight away - basically following the "convention over configuration", `docker compose up` should run for me fully prepared and ready-to-use application. Probably the best way to achieve that is docker entrypoint.
- Database migrations would be helpful in maintaining long-term project + some "fancy" solution for testing data, like data fixtures, but what we have here is good enough for demonstration.
