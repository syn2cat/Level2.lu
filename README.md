# Level2.lu
A website for the Level2 Hackspace

##Usage
- **[<code>GET</code> events/json](https://level2.lu/events/json)**
- **[<code>GET</code> events/:COUNT.json](https://level2.lu/events/42.json)**
- **[<code>GET</code> events/:YEAR/:MONTH.json](https://level2.lu/events/2015/03.json)**
- **[<code>GET</code> spaceapi](https://level2.lu/spaceapi)**

every event includes the following information:
- <code>start</code> the unix timestamp of when the event starts
- <code>end</code> the unix timestamp of when the event ends
- <code>date</code> A pre-formatted date that is used on the Level2.lu website
- <code>location</code> The location of the event
- <code>image</code> url to an image for the event, false when missing
- <code>url</code> url to more information about the event, false when missing
- <code>description</code> description of the event

## Examples

    https://level2.lu/events/json
    https://level2.lu/events/42.json
    https://level2.lu/events/2015/03.json
    https://level2.lu/spaceapi
