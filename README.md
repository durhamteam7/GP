# GP
Group Project

## Death by Modal Part 2
> One Modal to rule them all,
> One Modal to find them;
> One Modal to bring them all
> and in the darkness bind them.

Algorithm is in tests/ directory

## Main idea of algorithm
> Each image was circulated to multiple users and retired after meeting the following criteria:
> the first five classifications were “nothing here” (blank);
> ten non-consecutive “nothing here” classifications (blank_consensus);
> ten matching classifications of species or species - combination, not necessarily consecutive (consensus).
> If none of these criteria were met, the image was circulated until accumulating 25 species classifications (complete).

### Preliminary new table structure
newTable {
    retired : Boolean                            // true => has been decided to not contain any animals, false => has either been decided or still needs classifying
    classification : String                      // if empty string, we need to have more classifications, otherwise this is the decided animal for this image
    number_of_classifications : Number           // number of classifications for this image
    evenness : Number (0,1)                      // pielou evenness index
    fraction_blanks : Number (0,1)               // the fraction of blank classifications "nothing here" for an image that was ultimately classified as containing an animal
    fraction_support : Number (0,1)              // fraction of classifications supporting the aggregated answer (i.e. fraction support of 1.0 indicates unanimous support)
    tf_percentage : Number (0,1)                 // probably same as fraction_support
}