<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use function implode;

class CallableTypeNode implements TypeNode
{

	use NodeAttributes;

	public IdentifierTypeNode $identifier;

	/**
	 * What calling the callable may do, written in brackets after its name, like
	 * "Closure[pure](int): string"
	 */
	public ?TypeNode $purity;

	/** @var TemplateTagValueNode[] */
	public array $templateTypes;

	/** @var CallableTypeParameterNode[] */
	public array $parameters;

	public TypeNode $returnType;

	/**
	 * @param CallableTypeParameterNode[] $parameters
	 * @param TemplateTagValueNode[]  $templateTypes
	 */
	public function __construct(IdentifierTypeNode $identifier, array $parameters, TypeNode $returnType, array $templateTypes, ?TypeNode $purity = null)
	{
		$this->identifier = $identifier;
		$this->purity = $purity;
		$this->parameters = $parameters;
		$this->returnType = $returnType;
		$this->templateTypes = $templateTypes;
	}

	public function __toString(): string
	{
		$returnType = $this->returnType;
		if ($returnType instanceof self) {
			$returnType = "({$returnType})";
		}
		$template = $this->templateTypes !== []
			? '<' . implode(', ', $this->templateTypes) . '>'
			: '';
		$purity = $this->purity !== null ? "[{$this->purity}]" : '';
		$parameters = implode(', ', $this->parameters);
		return "{$this->identifier}{$purity}{$template}({$parameters}): {$returnType}";
	}

	/**
	 * @param array<string, mixed> $properties
	 */
	public static function __set_state(array $properties): self
	{
		$instance = new self($properties['identifier'], $properties['parameters'], $properties['returnType'], $properties['templateTypes'], $properties['purity'] ?? null);
		if (isset($properties['attributes'])) {
			foreach ($properties['attributes'] as $key => $value) {
				$instance->setAttribute($key, $value);
			}
		}
		return $instance;
	}

}
