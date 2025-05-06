# Introduction

laminas-filter provides a set of commonly needed data filters. It also provides a
simple filter chaining mechanism by which multiple filters may be applied to a
single datum in a user-defined order.

## What is a Filter?

In the physical world, a filter is typically used for removing unwanted portions
of input, and the desired portion of the input passes through as filter output
(e.g., coffee). In such scenarios, a filter is an operator that produces a
subset of the input. This type of filtering is useful for web applications:
removing illegal input, trimming unnecessary white space, etc.

This basic definition of a filter may be extended to include generalized
transformations upon input. A common transformation applied in web applications
is the escaping of HTML entities. For example, if a form field is automatically
populated with untrusted input (e.g., from a web browser), this value should
either be free of HTML entities or contain only escaped HTML entities, in order
to prevent undesired behavior and security vulnerabilities. To meet this
requirement, HTML entities that appear in the input must either be removed or
escaped. Of course, which approach is more appropriate depends on the situation.
A filter that removes the HTML entities operates within the scope of the first
definition of filter - an operator that produces a subset of the input. A filter
that escapes the HTML entities, however, transforms the input (e.g., `&` is
transformed to `&amp;`). Supporting such use cases for web developers is
important, and “to filter”, in the context of using laminas-filter, means to
perform some transformations upon input data.

Having this filter definition established provides the foundation for
`Laminas\Filter\FilterInterface`, which requires a single method named `filter()`
to be implemented by a filter class.
