<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;
use function sprintf;

class GenericTypeNode implements TypeNode
{

	public const VARIANCE_INVARIANT = 'invariant';
	public const VARIANCE_COVARIANT = 'covariant';
	public const VARIANCE_CONTRAVARIANT = 'contravariant';
	public const VARIANCE_BIVARIANT = 'bivariant';

	use NodeAttributes;

	public IdentifierTypeNode $type;

	/**
	 * What iterating or calling the type may do, written in brackets before the
	 * arguments, like "iterable[pure]<int, string>"
	 *
	 * @var TypeNode[]
	 */
	public array $purities;

	/** @var TypeNode[] */
	public array $genericTypes;

	/** @var (self::VARIANCE_*)[] */
	public array $variances;

	/**
	 * @param TypeNode[] $genericTypes
	 * @param (self::VARIANCE_*)[] $variances
	 * @param TypeNode[] $purities
	 */
	public function __construct(IdentifierTypeNode $type, array $genericTypes, array $variances = [], array $purities = [])
	{
		$this->type = $type;
		$this->purities = $purities;
		$this->genericTypes = $genericTypes;
		$this->variances = $variances;
	}

	public function __toString(): string
	{
		$genericTypes = [];

		foreach ($this->genericTypes as $index => $type) {
			$variance = $this->variances[$index] ?? self::VARIANCE_INVARIANT;
			if ($variance === self::VARIANCE_INVARIANT) {
				$genericTypes[] = (string) $type;
			} elseif ($variance === self::VARIANCE_BIVARIANT) {
				$genericTypes[] = '*';
			} else {
				$genericTypes[] = sprintf('%s %s', $variance, $type);
			}
		}

		$purities = $this->purities !== []
			? '[' . implode(', ', $this->purities) . ']'
			: '';

		return $this->type . $purities . '<' . implode(', ', $genericTypes) . '>';
	}

	/**
	 * @param array<string, mixed> $properties
	 */
	public static function __set_state(array $properties): self
	{
		$instance = new self($properties['type'], $properties['genericTypes'], $properties['variances'] ?? [], $properties['purities'] ?? []);
		if (isset($properties['attributes'])) {
			foreach ($properties['attributes'] as $key => $value) {
				$instance->setAttribute($key, $value);
			}
		}
		return $instance;
	}

}
