<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

#[AsSearch(<?php echo $index_name; ?>)]
class <?php echo $class_name; ?> extends AbstractSearch
{

    public function build(array $options = []): void
    {
        // ->addFacet('type', 'Type', null, ['limit' => 2])
        // ->addFacet('brand', 'Brand')
        // ->addFacet('rating', 'Rating')
    }
}
